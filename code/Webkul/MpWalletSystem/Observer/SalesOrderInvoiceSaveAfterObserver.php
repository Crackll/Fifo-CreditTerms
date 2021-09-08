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
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Magento\Framework\DB\Transaction;

/**
 * Webkul MpWalletSystem Observer Class
 */
class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
{
    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Mail          $mailHelper
     * @param \Webkul\MpWalletSystem\Helper\Data          $dataHelper
     * @param \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayments
     * @param \Webkul\Marketplace\Model\OrdersFactory     $mpOrderFactory
     * @param \Webkul\MpWalletSystem\Logger\Logger        $log
     * @param WalletUpdateData                            $walletUpdateData
     * @param InvoiceService                              $invoiceService
     * @param PaymentTransaction\BuilderInterface         $transactionBuilder
     * @param Transaction                                 $dbTransaction
     * @param WallettransactionFactory                    $walletTransaction
     * @param InvoiceSender                               $invoiceSender
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        \Webkul\MpWalletSystem\Helper\Data $dataHelper,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayments,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrderFactory,
        \Webkul\MpWalletSystem\Logger\Logger $log,
        WalletUpdateData $walletUpdateData,
        InvoiceService $invoiceService,
        PaymentTransaction\BuilderInterface $transactionBuilder,
        Transaction $dbTransaction,
        WallettransactionFactory $walletTransaction,
        InvoiceSender $invoiceSender
    ) {
        $this->mailHelper = $mailHelper;
        $this->dataHelper = $dataHelper;
        $this->mpOrderFactory = $mpOrderFactory;
        $this->walletTransaction = $walletTransaction;
        $this->splitPayments = $splitPayments;
        $this->logger = $log;
        $this->walletUpdateData = $walletUpdateData;
        $this->invoiceSender = $invoiceSender;
        $this->dbTransaction = $dbTransaction;
        $this->transactionBuilder = $transactionBuilder;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Invoice save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getInvoice();
        if (!$this->splitPayments->updateInvoice($invoice)) {
            return;
        }
        $order = $observer->getInvoice()->getOrder();
        $mpTracking = $this->mpOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId());
        try {
            //only whose invoice has been created and wallet in not invoiced
            foreach ($mpTracking as $sellerOrder) {
                //for order items
                if ($sellerOrder->getInvoiceId()
                    && !$sellerOrder->getWalletInvoiced()
                    && $sellerOrder->getWalletAmount()
                ) {
                    $sellerOrder->setWalletInvoiced(1);
                    $this->splitPayments->commitMethod($sellerOrder);
                    $walletTransaction = $this->walletTransaction->create();
                    $transferAmountData = [
                        'customer_id' => $sellerOrder->getSellerId(),
                        'walletamount' => $sellerOrder->getWalletAmount(),
                        'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                        'curr_code' => $order->getOrderCurrencyCode(),
                        'curr_amount' => $sellerOrder->getWalletAmount(),
                        'walletnote' => __('Order id : %1 credited amount', $order->getIncrementId()),
                        'sender_id' => 0,
                        'sender_type' => 3,
                        'order_id' => $order->getId(),
                        'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
                        'increment_id' => $order->getIncrementId()
                    ];
                    $this->walletUpdateData->creditAmount($sellerOrder->getSellerId(), $transferAmountData);
                    $this->splitPayments->createTransaction($order, $sellerOrder->getWalletAmount());
                } else {
                    continue;
                }
            }
            //check if all order items in marketplace_orders are invoiced, create invoice for admin shipping
        } catch (\Exception $e) {
            $this->logger->addInfo($e->getMessage());
        }
    }
}
