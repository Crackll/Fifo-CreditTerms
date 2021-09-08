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
namespace Webkul\MpSellerCategory\Plugin\CatalogSearch\Model\Search\SelectContainer;

class SelectContainer
{
    /**
     * @var \Webkul\MpSellerCategory\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_mpSellerCategoryCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpSellerCategory\Helper\Data $helper
     * @param \Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory
     * $mpSellerCategoryCollectionFactory,
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\MpSellerCategory\Helper\Data $helper,
        \Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory $mpSellerCategoryCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_helper = $helper;
        $this->_mpSellerCategoryCollectionFactory = $mpSellerCategoryCollectionFactory;
        $this->_resource = $resource;
        $this->_request = $request;
    }

    /**
     * beforeUpdateSelect plugin
     *
     * @param \Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer $subject
     * @param \Magento\Framework\DB\Select $select
     * @return void
     */
    public function beforeUpdateSelect(
        \Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer $subject,
        $select
    ) {
        try {
            if (!$this->_helper->isAllowedSellerCategoryFilter()) {
                return [$select];
            }

            $requestVar = $this->_helper->getRequestVar();
            $filter = trim($this->_request->getParam($requestVar));
            if ($filter != "") {
                $sellerCategoryCollection = $this->_mpSellerCategoryCollectionFactory->create();
                $joinTable = $this->_resource->getTableName('marketplace_seller_category_product');
                $sellerCategoryCollection->getSelect()->
                join($joinTable.' as scp', 'main_table.entity_id = scp.seller_category_id');
                $sellerCategoryCollection->addFieldToFilter("scp.seller_category_id", $filter);
                $sellerCategoryCollection->addFieldToFilter("status", 1);
                $sellerCategoryCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('scp.product_id');
                $sellerCategoryCollection->getSelect()->group("scp.product_id");
                $query = $sellerCategoryCollection->getSelect()->__toString();
                $select = $select->where("search_index.entity_id in ($query)");
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return [$select];
    }
}
