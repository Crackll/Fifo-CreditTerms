<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceProductLabels\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
 
class ProductLabel extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'product/edit/productLabel.phtml';
 
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\MarketplaceProductLabels\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Webkul\MarketplaceProductLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param \Webkul\MarketplaceProductLabels\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Webkul\MarketplaceProductLabels\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        array $data = []
    ) {
        $this->labelFactory = $labelFactory;
        $this->_coreRegistry = $registry;
        $this->helper = $helper;
        $this->product = $productFactory;
        $this->mpProductFactory = $mpProductFactory;
        parent::__construct($context, $data);
    }

    /**
     * Return productlabel_id attribute of product
     *
     * @return text
     */
    public function getProductLabelId()
    {
        $productId = $this->getRequest()->getParam("id");
        if ($productId) {
            $attr = $this->product->create()->load($productId);
            return   $attr->getProductLabelId();
        }
    }

    /**
     * Return base url of media folder
     *
     * @return String
     */
    public function getBaseUrl()
    {
        return $this->helper->getMediaFolder();
    }

    /**
     * Return Seller's Product Label
     *
     * @return collection
     */
    public function getProductLabels()
    {
        $id = $this->getRequest()->getParam('id');
        $product = $this->mpProductFactory->create()->load($id, 'mageproduct_id');
        if (!empty($product->getSellerId())) {
            $sellerId = $product->getSellerId();
        } else {
            $sellerId = 0;
        }
        $collection=$this->labelFactory->create()->getCollection()
                    ->addFieldToFilter('status', ['eq' => '1'])
                    ->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        
        return $collection;
    }
}
