<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c)   Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Plugin\Checkout\Block\Cart;

class GetCartItemHtml
{
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_helper;

    /**
     * Undocumented variable
     *
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_item;

    /**
     * @param \Webkul\MpAdvertisementManager\Helper\Data $helper
     * @param \Magento\Checkout\Model\Cart               $item
     */
    public function __construct(
        \Webkul\MpAdvertisementManager\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $item
    ) {
        $this->_helper = $helper;
        $this->_item = $item;
    }

    /**
     * plugin to disable the quantity input element so that it's quantity cannot be chabge and remain always 1
     * of caret item.
     *
     * @param \Magento\Checkout\Block\Cart
     * @param Closure              $result
     * @param \Magento\Quote\Model\Quote\Item
     * @return html
     */
    public function aroundGetItemHtml(
        \Magento\Checkout\Block\Cart $subject,
        $proceed,
        \Magento\Quote\Model\Quote\Item $item
    ) {
        if ($item->getSku() == "wk_mp_ads_plan") {
            return $proceed($item);
        } else {
            return $proceed($item);
        }
    }
}
