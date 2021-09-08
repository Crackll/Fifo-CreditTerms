<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Helper\Data;
use Webkul\MpWholesale\Model\ProductFactory as WholeSaleProductFactory;

class View extends \Magento\Framework\View\Element\Template
{
    /*
    * @var \Magento\Catalog\Model\Product
    */
    protected $_productlists;
    
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $attributeModel;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;
    
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;
    
    /**
     * @var \Webkul\MpWholesale\Model\ProductFactory as WholeSaleProductFactory
     */
    protected $wholeSaleProductFactory;
    
    /**
     * @param Context $context
     * @param Attribute $attributeModel
     * @param ProductFactory $productFactory
     * @param CollectionFactory $productCollection
     * @param Data $marketplaceHelper
     * @param WholeSaleProductFactory $wholeSaleProductFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Attribute $attributeModel,
        ProductFactory $productFactory,
        CollectionFactory $productCollection,
        Data $marketplaceHelper,
        WholeSaleProductFactory $wholeSaleProductFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->imageHelper = $context->getImageHelper();
        $this->attributeModel = $attributeModel;
        $this->productFactory = $productFactory;
        $this->productCollection = $productCollection;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->wholeSaleProductFactory = $wholeSaleProductFactory;
    }
    
    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        $storeId = $this->marketplaceHelper->getCurrentStoreId();
        $storeId = 0;

        $websiteId = $this->marketplaceHelper->getWebsiteId();
        if (!($customerId = $this->marketplaceHelper->getCustomerId())) {
            return false;
        }
        if (!$this->_productlists) {
            $paramData = $this->getRequest()->getParams();
            $filter = '';
            if (isset($paramData['s'])) {
                $filter = $paramData['s'] != '' ? $paramData['s'] : '';
            }
            $proAttId = $this->attributeModel->getIdByCode('catalog_product', 'name');

            /* Get WholeSaler's Product Collection */
            $wholeSaleProductCollection = $this->wholeSaleProductFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('approve_status', 1)
            ->addFieldToSelect(['product_id'])
            ->distinct(true);

            $catalogProductEntityVarchar = $this->wholeSaleProductFactory->create()
                                        ->getCollection()->getTable('catalog_product_entity_varchar');
            $wholeSaleProductCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.product_id = cpev.entity_id'
            )->where(
                'cpev.store_id = '.$storeId.' AND
                cpev.value like "%'.$filter.'%" AND
                cpev.attribute_id = '.$proAttId
            );

            $catalogProductEntity = $this->wholeSaleProductFactory->create()
                                    ->getCollection()->getTable('catalog_product_entity');
            $wholeSaleProductCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.product_id = cpe.entity_id',
                [
                    'entity_id' => 'main_table.entity_id'
                ]
            );

            $joinTable = $this->productCollection->create()
                                    ->getTable('admin_user');
            $wholeSaleProductCollection->getSelect()->joinLeft(
                $joinTable.' as cgf',
                'main_table.user_id = cgf.user_id',
                [
                    'wholesaler_name'=>'cgf.firstname'
                ]
            );
            $wholeSaleProductCollection->getSelect()->where(
                'cgf.is_active = 1 AND
                main_table.approve_status = 1'
            );
            $this->_productlists = $wholeSaleProductCollection;
        }
        
        return $this->_productlists;
    }
    
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'wholesaler.product.view.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
        }
        
        return $this;
    }

    /**
     * Get Product Data by Id
     *
     * @param string $id
     * @return object
     */
    public function getProductData($id = '')
    {
        return $this->productFactory->create()->load($id);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function imageHelperObj()
    {
        return $this->imageHelper;
    }
}
