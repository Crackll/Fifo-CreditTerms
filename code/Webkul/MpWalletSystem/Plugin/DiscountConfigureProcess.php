<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Plugin;

/**
 * Class DiscountConfigureProcess
 *
 * Removes discount block when wallet amount product is in cart.
 */
class DiscountConfigureProcess
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    private $walletHelper;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data $walletHelper
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $walletHelper
    ) {
        $this->walletHelper = $walletHelper;
    }

    /**
     * Checkout LayoutProcessor before process plugin.
     *
     * @param  \Magento\Checkout\Block\Checkout\LayoutProcessor $processor
     * @param  array                                            $jsLayout
     * @return array
     */
    public function aroundProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $LayoutProcessor,
        callable $proceed,
        $jsLayout
    ) {
        $jsLayout = $proceed($jsLayout);
        if (!$this->walletHelper->getDiscountEnable() && !$this->walletHelper->getCartStatus()) {
            unset(
                $jsLayout['components']['checkout']['children']
                ['steps']['children']['billing-step']
                ['children']['payment']['children']
                ['afterMethods']['children']['discount']
            );
            unset(
                $jsLayout['components']['checkout']['children']
                ['steps']['children']['billing-step']
                ['children']['payment']['children']
                ['afterMethods']['children']['reward_amount']
            );
        }
        return $jsLayout;
    }
}
