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
namespace Webkul\MarketplaceProductLabels\Block\Label;

use Magento\Framework\View\Element\Template\Context;
use Webkul\MarketplaceProductLabels\Helper\Data as Helper;
use Webkul\MarketplaceProductLabels\Model\LabelFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Catalog\Model\ProductFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

/**
 * DefaultLabel block content block
 */
class Labellist extends \Magento\Framework\View\Element\Template
{

    /**
     * Helper
     *
     * @var \Webkul\MarketplaceProductLabels\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MarketplaceProductLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \MpHelper
     */
    protected $mpHelper;
    
    /**
     * @param Context $context
     * @param Helper $helper
     * @param LabelFactory $labelFactory
     * @param SessionFactory $customerSessionFactory
     * @param ProductFactory $productFactory
     * @param MpHelper $mpHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        LabelFactory $labelFactory,
        SessionFactory $customerSessionFactory,
        ProductFactory $productFactory,
        MpHelper $mpHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->labelFactory = $labelFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->productFactory = $productFactory;
        $this->mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $collection = $this->getSellerLabelList();
        $this->setCollection($collection);
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class)
                        ->setAvailableLimit([10=>10,20=>20,30=>30])
                        ->setCollection($this->getCollection());
            $this->setChild('pager', $pager);
        }
        return $this;
    }

    /**
     * Get Seller's Label Collection
     *
     * @return labelCollection
     */
    public function getLabelList()
    {
        $sellerId = $this->getSellerId();
        $labelCollection = $this->labelFactory->create()->getCollection()
                            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                            ->addFieldToFilter('status', ['eq' => 1]);
        return $labelCollection;
    }

    /**
     * Get Seller's Label Collection
     *
     * @return labelCollection
     */
    public function getSellerLabelList()
    {
        $sellerId = $this->getSellerId();
        $labelCollection = $this->labelFactory->create()->getCollection()
                            ->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        $paramData = $this->getRequest()->getParams();
        $filterPosition = isset($paramData['label_filterposition'])!= '' ? $paramData['label_filterposition'] : '';
        $filterStatus = isset($paramData['label_filterstatus'])!= '' ? $paramData['label_filterstatus'] : '';
        if (isset($paramData['label_filter'])) {
            $filter = $paramData['label_filter'] != '' ? $paramData['label_filter'] : '';
            $labelCollection->addFieldToFilter('label_name', ['like' => '%'.$filter.'%']);
                                
        }
        if ($filterPosition) {
            $labelCollection->addFieldToFilter('position', ['eq' => $filterPosition]);
        }
        if ($filterStatus != 4 && $filterStatus != null) {
            $labelCollection->addFieldToFilter('status', ['eq' => $filterStatus]);
        }
        return $labelCollection;
    }

    /**
     * Get Seller Id
     *
     * @return text
     */
    public function getSellerId()
    {
        return $this->customerSessionFactory->create()->getCustomerId();
    }
    
    /**
     * Get Label Data
     *
     * @param [type] $id
     * @return labelData
     */
    public function getLabelData($id)
    {
        $labelData = $this->labelFactory->create()->getCollection()->addFieldToFilter('id', ['eq' => $id]);
        return $labelData;
    }
    
    /**
     * Get Media URL
     *
     * @param [type] $src
     * @return string
     */
    public function getMediaUrl($src)
    {
        $imgSrc = $this->helper->getMediaFolder().'/mplabel/label/'.$src;
        return $imgSrc;
    }

    /**
     * Get Media Base Folder
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->helper->getMediaFolder();
    }
    
    /**
     * Get Product Label Id, if Label Selected for Product
     *
     * @return ProductLabelId
     */
    public function getProductLabelId()
    {
        $productId = $this->getRequest()->getParam("id");
        if ($productId) {
            $attr = $this->productFactory->create()->load($productId);
            return   $attr->getProductLabelId();
        }
    }

    /**
     *
     * @return pager
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject($helper = '')
    {
        if ($helper == 'marketplaceHelper') {
            return $this->mpHelper;
        } else {
            return $this->helper;
        }
    }
}
