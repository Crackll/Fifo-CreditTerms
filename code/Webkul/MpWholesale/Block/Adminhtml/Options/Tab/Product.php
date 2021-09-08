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

namespace Webkul\MpWholesale\Block\Adminhtml\Options\Tab;

use Magento\Customer\Controller\RegistryConstants;

class Product extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $_sellerProduct;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Webkul\MpWholesale\Model\ProductFactory
     */
    protected $mpWholeSaleProduct;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\MpWholesale\Model\ProductFactory $mpWholeSaleProduct
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpWholesale\Model\ProductFactory $mpWholeSaleProduct,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_productFactory = $productFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->mpWholeSaleProduct = $mpWholeSaleProduct;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('template_product_grid');
        $this->setDefaultSort('entity_at');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $userId = $this->_backendHelper->getCurrentUserId();
        $productId = $this->getProductId();
        $collection = $this->mpWholeSaleProduct ->create()->getCollection()
                      ->addFieldToFilter('user_id', $userId)->addFieldToSelect('product_id');
        $productIds = [];
        foreach ($collection as $products) {
            $productIds[] = $products->getProductId();
        }
        $collection = $this->_productFactory->create()->getCollection()
        ->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'entity_id'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        )->addAttributeToSelect(
            'price_type'
        )->addFieldToFilter(
            'type_id',
            ['nin' => ['grouped','bundle']]
        );
        if ($productId) {
            $collection->addFieldToFilter('entity_id', ['eq' => $productId]);
        } elseif (!empty($productIds)) {
            $collection->addFieldToFilter('entity_id', ['nin' => $productIds]);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'products_id',
            [
                'type' => 'checkbox',
                'name' => 'products_id',
                'required'  => true,
                'data-form-part' => $this->getData('wholesale_product_form'),
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'sortable' => true
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Product SKU'),
                'index' => 'sku'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Product Price'),
                'index' => 'price',
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $row->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    protected function _getSelectedProducts()
    {
        $products = array_values($this->getSelectedProducts());
        return $products;
    }

    public function getSelectedProducts()
    {
        $productId = $this->getProductId();
        $proIds = ['entity_id' => $productId];
        return $proIds;
    }

    public function getProductId()
    {
        $id = $this->getRequest()->getParam('id');
        $productModel = $this->mpWholeSaleProduct->create()->load($id);
        return $productModel->getProductId() ? $productModel->getProductId() : 0;
    }
}
