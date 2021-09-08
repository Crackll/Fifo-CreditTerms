<?php

namespace Webkul\MpAuction\Rewrite;

use \Magento\Catalog\Model\Product;
use \Magento\Framework\View\Element\Template;

class WishList extends \Magento\Catalog\Pricing\Render
{
 
    private $helperData;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\MpAuction\Helper\Data $helperData,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->helperData = $helperData;
        $this->request = $request;
        parent::__construct($context, $registry, $data);
    }

    protected function _toHtml()
    {
        if ($this->request->getModuleName()=='wishlist') {
            $priceRender = $this->getLayout()->getBlock($this->getPriceRender());
            if ($priceRender instanceof \Magento\Framework\Pricing\Render) {
                $product = $this->getProduct();
                if ($product instanceof \Magento\Framework\Pricing\SaleableInterface) {
                    $arguments = $this->getData();
                    $arguments['render_block'] = $this;
                    $price =  $priceRender->render($this->getPriceTypeCode(), $product, $arguments);
                    $auctionDetail = $this->helperData->getProductAuctionDetail($product);
                    if ($auctionDetail) {
                        return $auctionDetail;
                    } else {
                        return parent::_toHtml();
                    }
                }
            }
        }
        return parent::_toHtml();
    }
}
