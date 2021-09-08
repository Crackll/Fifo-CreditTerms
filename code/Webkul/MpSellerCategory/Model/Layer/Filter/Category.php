<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;

class Category extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Webkul\MpSellerCategory\Model\CategoryFactory
     */
    protected $_mpSellerCategoryFactory;

    /**
     * @var \Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_mpSellerCategoryCollectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpSellerCategory\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param CategoryFactory $categoryDataProviderFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory
     * @param \Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory $mpSellerCategoryCollectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\MpSellerCategory\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        CategoryFactory $categoryDataProviderFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory,
        \Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory $mpSellerCategoryCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpSellerCategory\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
        $this->_storeManager = $storeManager;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->_resource = $resource;
        $this->_mpSellerCategoryFactory = $mpSellerCategoryFactory;
        $this->_mpSellerCategoryCollectionFactory = $mpSellerCategoryCollectionFactory;
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_requestVar = $this->_helper->getRequestVar();
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * Apply category filter to layer
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        $sellerCategoryCollection = $this->_mpSellerCategoryCollectionFactory->create();
        $joinTable = $this->_resource->getTableName('marketplace_seller_category_product');
        $sellerCategoryCollection->getSelect()
        ->join(
            $joinTable.' as scp',
            'main_table.entity_id = scp.seller_category_id'
        );
        $sellerCategoryCollection->addFieldToFilter("scp.seller_category_id", $filter);
        $sellerCategoryCollection->addFieldToFilter("status", 1);
        $sellerCategoryCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('scp.product_id');
        $sellerCategoryCollection->getSelect()->group("scp.product_id");
        $productIds = $sellerCategoryCollection->getData();
        $collection = $this->getLayer()->getProductCollection();
        $collection->addAttributeToFilter("entity_id", ['in' => $productIds]);
        $name = $this->getCategoryName($filter);
        $this->getLayer()->getState()->addFilter($this->_createItem($name, $filter));
        return $this;
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_helper->getCategoryFilterDisplayName();
    }

    /**
     * Get data array for building attribute filter items
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->_helper->isSellerCategoryFilterActive()) {
            return $this->itemDataBuilder->build();
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->getLayer()->getProductCollection();

        $cloneCollection = clone $collection;
        $cloneCollection->getSelect()
            ->reset(\Zend_Db_Select::LIMIT_COUNT)
            ->reset(\Zend_Db_Select::LIMIT_OFFSET)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->reset(\Zend_Db_Select::ORDER)
            ->columns('e.entity_id');

        $query = $cloneCollection->getSelectSql(true);
        $connection = $this->_resource->getConnection();
        $result = $connection->fetchAll($query);
        $productIds = [];
        foreach ($result as $row) {
            $productIds[] = $row['entity_id'];
        }

        $collection = $this->_mpSellerCategoryCollectionFactory->create();
        $joinTable = $this->_resource->getTableName('marketplace_seller_category_product');
        $fields = ['count(scp.entity_id) as count'];
        $collection->getSelect()->join($joinTable.' as scp', 'main_table.entity_id = scp.seller_category_id', $fields);
        $collection->addFieldToFilter("scp.product_id", ["in" => $productIds]);
        $collection->addFieldToFilter("status", 1);
        $collection->getSelect()->group("category_name");
        $collection->getSelect()->order("position", "ASC");

        foreach ($collection as $item) {
            $this->itemDataBuilder->addItemData($item->getCategoryName(), $item->getId(), $item->getCount());
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * Get Category Name by Category Id
     *
     * @param integer $categoryId
     *
     * @return string
     */
    public function getCategoryName($categoryId)
    {
        $category = $this->_mpSellerCategoryFactory->create()->load($categoryId);
        if (!empty($category->getId())) {
            return $category->getCategoryName();
        }

        return $categoryId;
    }
}
