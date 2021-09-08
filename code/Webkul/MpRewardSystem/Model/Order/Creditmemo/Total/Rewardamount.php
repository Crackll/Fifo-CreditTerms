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
namespace Webkul\MpRewardSystem\Model\Order\Creditmemo\Total;

class Rewardamount extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $orderRewardamountTotal = $order->getMprewardAmount();

        if ($orderRewardamountTotal) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$orderRewardamountTotal);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$orderRewardamountTotal);
        }
        return $this;
    }
}
