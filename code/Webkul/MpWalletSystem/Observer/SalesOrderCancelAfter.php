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
use Magento\Sales\Model\OrderFactory;
use \Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Webkul\MpWalletSystem\Model\WalletrecordFactory;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use \Webkul\MpWalletSystem\Model\Wallettransaction;

/**
 * Webkul MpWalletSystem Observer Class
 */
class SalesOrderCancelAfter implements ObserverInterface
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Mail
     */
    protected $mailHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Webkul\MpWalletSystem\Model\WallettransactionFactory
     */
    protected $walletTransaction;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletrecordFactory;
     */
    protected $walletrecord;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;
    
    /**
     * Initialize dependencies
     *
     * @param OrderFactory                                $orderFactory
     * @param \Webkul\MpWalletSystem\Helper\Mail          $mailHelper
     * @param \Webkul\MpWalletSystem\Helper\Data          $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param WallettransactionFactory                    $wallettransaction
     * @param WalletrecordFactory                         $walletRecord
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        WallettransactionFactory $wallettransaction,
        WalletrecordFactory $walletRecord,
        WalletUpdateData $walletUpdateData
    ) {
        $this->orderFactory = $orderFactory;
        $this->mailHelper = $mailHelper;
        $this->helper = $helper;
        $this->date = $date;
        $this->walletTransaction = $wallettransaction;
        $this->walletrecord = $walletRecord;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * Credit memo save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $orderId = $order->getEntityId();
        $incrementId = $order->getIncrementId();
        $walletAmount = 0;
        foreach ($order->getInvoiceCollection() as $previousInvoice) {
            if ((double) $previousInvoice->getWalletAmount() && !$previousInvoice->isCanceled()) {
                $walletAmount = $walletAmount + $previousInvoice->getWalletAmount();
            }
        }
        if ($order->getWalletAmount()!=$walletAmount) {
            $walletAmount = $order->getWalletAmount() - $walletAmount;
        } else {
            $walletAmount = 0;
        }

        $totalCanceledAmount = (-1 * $walletAmount);
        $baseTotalCanceledAmount = $this->helper->baseCurrencyAmount($totalCanceledAmount);
        $currencyCode = $order->getOrderCurrencyCode();
        $rowId = 0;
        $totalAmount = 0;
        $remainingAmount = 0;
        $orderItem = $order->getAllItems();
        $productIdArray = [];

        foreach ($orderItem as $value) {
            $productIdArray[] = $value->getProductId();
        }
        $walletProductId = $this->helper->getWalletProductId();

        if (!in_array($walletProductId, $productIdArray) && $walletAmount != 0) {
            $customerId = $order->getCustomerId();
            $transferAmountData = [
                'customer_id' => $customerId,
                'walletamount' => $baseTotalCanceledAmount,
                'walletactiontype' => Wallettransaction::WALLET_ACTION_TYPE_CREDIT,
                'curr_code' => $currencyCode,
                'curr_amount' => $totalCanceledAmount,
                'walletnote' => __('Order id : %1 credited amount', $incrementId),
                'sender_id' => 0,
                'sender_type' => Wallettransaction::REFUND_TYPE,
                'order_id' => $orderId,
                'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
                'increment_id' => $incrementId
            ];
            $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
        }
    }
}
