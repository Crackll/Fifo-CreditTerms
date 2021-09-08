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

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul MpWalletSystem Observer Class
 */
class Quotesubmitbefore implements ObserverInterface
{
    /**
     * Walletsystem event handler
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletAmount = $observer->getQuote()->getShippingAddress()->getWalletAmount();
        $baseWalletAmount = $observer->getQuote()->getShippingAddress()->getBaseWalletAmount();
        if ($walletAmount==null || $walletAmount==0) {
            $walletAmount = $observer->getQuote()->getBillingAddress()->getWalletAmount();
            $baseWalletAmount = $observer->getQuote()->getBillingAddress()->getBaseWalletAmount();
        }
        $order = $observer->getOrder();
        $order->setWalletAmount($walletAmount);
        $order->setBaseWalletAmount($baseWalletAmount);
    }
}
