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

namespace Webkul\MpWalletSystem\Model\Total;

/**
 * Webkul MpWalletSystem Model Class
 */
class Invoicetotal extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $walletHelper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Webkul\MpWalletSystem\Helper\Data  $walletHelper
     * @param array                               $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $httpRequest,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->httpRequest = $httpRequest;
        $this->walletHelper = $walletHelper;
    }

    /**
     * Collect invoice Wallet amount.
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     *
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $params = $this->httpRequest->getParams();
        $invoiceParams = [];
        if (array_key_exists('invoice', $params)) {
            $invoiceParams = $params['invoice']['items'];
        }
        $order = $invoice->getOrder();
        $walletAmount = 0;
        $invoiceAmount = 0;
        $shippingAmount = 0;
        $taxAmount = 0;
        $balance = $order->getWalletAmount();
        $baseWallet = $order->getBaseWalletAmount();
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ((double) $previousInvoice->getWalletAmount() && !$previousInvoice->isCanceled()) {
                $walletAmount = $walletAmount + $previousInvoice->getWalletAmount();
                $shippingAmount = $shippingAmount + $previousInvoice->getShippingAmount();
                $taxAmount = $taxAmount + $previousInvoice->getTaxAmount();
            }
            if ($walletAmount == $order->getWalletAmount()) {
                return $this;
            }
        }
        $balance = -1 * $balance;
        $invoiceAmount = $this->getInvoiceAmount($order->getAllItems(), $invoiceParams);
        $finalAmount = $invoiceAmount +
                        ($order->getShippingAmount() - $shippingAmount) +
                        ($order->getTaxAmount() - $taxAmount);
        $finalWalletAmount = $balance - (-$walletAmount);
        if ($finalAmount <= $finalWalletAmount) {
            $balance = $finalAmount;
        } else {
            $balance = $finalWalletAmount;
        }
        $walletHelper = $this->walletHelper;
        $baseCurrency = $walletHelper->getBaseCurrencyCode();
        $orderCurrency = $order->getOrderCurrencyCode();
        $baseBalance = $walletHelper->getwkconvertCurrency($orderCurrency, $baseCurrency, $balance);
        $balance = -1 * $balance;
        $baseBalance = -1 * $baseBalance;
        $invoice->setWalletAmount($balance);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $balance);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseBalance);
        return $this;
    }

    /**
     * Get Invoice Amount function
     *
     * @param array $items
     * @param array $invoiceParams
     * @return int
     */
    public function getInvoiceAmount($items, $invoiceParams)
    {
        $invoiceAmount = 0;
        foreach ($items as $item) {
            if (array_key_exists($item->getItemId(), $invoiceParams)) {
                $price = $item->getprice() * $invoiceParams[$item->getItemId()];
                $tax = $item->getTaxAmount()/$item->getQtyOrdered();
                $price = $price + $tax * $invoiceParams[$item->getItemId()];
                $invoiceAmount = $invoiceAmount + $price;
            } else {
                $params = [];
                $qty = 1;
                if ($item->getProductOptions()) {
                    $productOptions = $item->getProductOptions();
                    if (is_array($productOptions)) {
                        $params = $productOptions['info_buyRequest'];
                    }
                }
                if (array_key_exists('qty', $params)) {
                    $qty = $params['qty'];
                }
                $price = $item->getprice() * $qty;
                $tax = $item->getTaxAmount()/$item->getQtyOrdered();
                $price = $price + $tax * $qty;
                $invoiceAmount = $invoiceAmount + $price;
            }
        }
        return $invoiceAmount;
    }
}
