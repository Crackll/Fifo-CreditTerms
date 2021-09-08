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
class Title
{

    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data       $helper
     * @param \Webkul\MpWalletSystem\Model\AdminWallet $adminWallet
     * @param \Webkul\Marketplace\Model\Orders         $mpOrders
     * @param \Magento\Sales\Model\Order               $order
     * @param \Magento\Sales\Model\Order\Invoice       $inovice
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Webkul\MpWalletSystem\Model\AdminWallet $adminWallet,
        \Webkul\Marketplace\Model\Orders $mpOrders,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Invoice $inovice
    ) {
        $this->inovice = $inovice;
        $this->adminWallet = $adminWallet;
        $this->mpOrders = $mpOrders;
        $this->helper = $helper;
        $this->order = $order;
    }

    /**
     * After Get Title function
     *
     * @param \Magento\Sales\Model\Order\Payment $subject
     * @param string $result
     * @return string $result
     */
    public function afterGetTitle(\Magento\Payment\Model\Method\AbstractMethod $subject, $result)
    {
        return $checkIfPartialWalletOrder = $this->checkOrder($result);
    }

    /**
     * Check Order function
     *
     * @param string $result
     * @return string $result
     */
    public function checkOrder($result)
    {
        $orderId = $this->helper->getOrderIdFromUrl();
        if ($orderId == "") {
            $invoiceId = $this->helper->checkInvoiceIdInUrl();
            if (!$invoiceId) {
                return $result;
            } else {
                $salesInvoiceCollection = $this->getInvoiceFromId($invoiceId);
                foreach ($salesInvoiceCollection as $salesInvoice) {
                    $orderId = $salesInvoice->getOrderId();
                }
            }
        }
        $salesOrder = $this->order;
        $order = $salesOrder->load($orderId);
        if (-$order->getWalletAmount() > 0) {
            if (isset($invoiceId)) {
                $mpOrders = $this->mpOrders;
                $mpOrderCollection = $mpOrders->getCollection()
                    ->addFieldToFilter('order_id', $order->getId())
                    ->addFieldToFilter('invoice_id', $invoiceId);
                if (!$mpOrderCollection->getSize()) {
                    $adminWalletCollection = $this->adminWallet->getCollection()
                        ->addFieldToFilter('type', $this->adminWallet::PAY_TYPE_ORDER_AMOUNT)
                        ->addFieldToFilter('order_id', $order->getId());
                    foreach ($adminWalletCollection as $adminWallet) {
                        $walletAmount = $adminWallet->getAmount();
                    }
                }
                foreach ($mpOrderCollection as $mpOrder) {
                    $walletAmount = $mpOrder->getWalletAmount();
                }
                $salesInvoiceCollection = $this->getInvoiceFromId($invoiceId);
                $invoice = $this->getInvoiveFromCollection($salesInvoiceCollection);
                $walletTitle = $this->helper->getWalletTitle()." (".$walletAmount.")";
                $remainingAmount = $invoice->getGrandTotal() - $walletAmount;
                $currency = $order->getOrderCurrencyCode();
                return $result." (".$remainingAmount.") + ".$walletTitle;
            } else {
                $walletTitle = $this->helper->getWalletTitle();
                $walletAmount = -$order->getWalletAmount();
                $remainingAmount = $order->getGrandTotal() - $walletAmount;
                if ($result != __($walletTitle)) {
                    return $result." (".$remainingAmount.") + ".$walletTitle." (".$walletAmount.")";
                }
            }
        }
        return $result;
    }

    /**
     * Get Invoice From Invoice Id function
     *
     * @param int $invoiceId
     * @return collection
     */
    public function getInvoiceFromId($invoiceId)
    {
        return $this->inovice
            ->getCollection()
            ->addFieldToFilter('entity_id', $invoiceId);
    }

    /**
     * Get Invoice From Invoice Collection function
     *
     * @param array $salesInvoiceCollection
     * @return collection
     */
    public function getInvoiveFromCollection($salesInvoiceCollection)
    {
        $invoice = "";
        foreach ($salesInvoiceCollection as $salesInvoice) {
            $invoice = $salesInvoice;
        }
        return $invoice;
    }
}
