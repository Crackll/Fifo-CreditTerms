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
namespace Webkul\MpRewardSystem\Model\Order\Invoice\Total;

class Rewardamount extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order=$invoice->getOrder();
        $orderRewardamountTotal = $order->getMprewardAmount();
        if ($orderRewardamountTotal && $order->getInvoiceCollection()->getSize() ==0) {
            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderRewardamountTotal);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$orderRewardamountTotal);
        }
        return $this;
    }
}
