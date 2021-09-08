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

namespace Webkul\MpWalletSystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul MpWalletSystem Observer Class
 */
class RemoveBlockForDiscount implements ObserverInterface
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $walletHelper;

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
     * Controller's execute function
     *
     * @return redirect
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Framework\View\Layout $layout
         */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('checkout.cart.coupon');

        if ($block) {
            if (!$this->walletHelper->getDiscountEnable() && !$this->walletHelper->getCartStatus()) {
                $layout->unsetElement('checkout.cart.coupon');
                return $this;
            }
            return $this;
        }
        return $this;
    }
}
