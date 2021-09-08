<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Plugin\Quote;

class AddProduct
{
    /**
     * object manager for injecting objects.
     *
     * @var Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\UrlInterface           $urlinterface
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
    
        $this->_objectManager = $objectManager;
    }

    /**
     * plugin to stop add to cart of ads plan product
     *
     * @param \Magento\Quote\Model\Quote
     * @param Closure                       $proceed
     * @param Magento\Catalog\Model\Product $product
     *
     * @return html
     */
    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
        if ($product->getSku() == 'wk_mp_ads_plan' && is_object($request)) {
            $price=0;
            $arr=$request->getData();
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $key1 => $value1) {
                        if ($key1=="price") {
                            $price=$value1;
                        }
                    }
                }
            }
            $product->setPrice($price);
            $product->save();
        }
        if ($product->getSku() == 'wk_mp_ads_plan' && $request === null) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This product cannot be added from here')
            );
        }
    }
}
