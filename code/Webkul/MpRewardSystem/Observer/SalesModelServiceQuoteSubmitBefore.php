<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $getQuote = $observer->getQuote();
        $rewardAmount = $getQuote->getMprewardAmount();
        $baseRewardAmount = $getQuote->getBaseMprewardAmount();
        $order = $observer->getOrder();
        $order->setMprewardAmount($rewardAmount);
        $order->setBaseMprewardAmount($baseRewardAmount);
    }
}
