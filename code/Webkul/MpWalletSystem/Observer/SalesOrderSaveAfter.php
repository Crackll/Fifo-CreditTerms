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
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Webkul\MpWalletSystem\Model\WalletrecordFactory;
use Magento\Sales\Model\OrderFactory;
use Webkul\MpWalletSystem\Model\WalletUpdateData;

/**
 * Webkul MpWalletSystem Observer Class
 */
class SalesOrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Mail
     */
    protected $mailHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletcreditamountFactory
     */
    protected $walletcreditAmountFactory;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WallettransactionFactory
     */
    protected $walletTransactionFactory;
    
    /**
     * @var WalletrecordFactory
     */
    protected $wallerRecordFactory;
    
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $salesOrderFactory;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;
    
    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime            $date
     * @param \Webkul\MpWalletSystem\Helper\Data                     $helper
     * @param \Webkul\MpWalletSystem\Helper\Mail                     $mailHelper
     * @param \Webkul\MpWalletSystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
     * @param WallettransactionFactory                               $walletTransaction
     * @param WalletrecordFactory                                    $walletRecord
     * @param OrderFactory                                           $orderFactory
     * @param WalletUpdateData                                       $walletUpdateData
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayment,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        \Webkul\MpWalletSystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        WallettransactionFactory $walletTransaction,
        WalletrecordFactory $walletRecord,
        OrderFactory $orderFactory,
        WalletUpdateData $walletUpdateData
    ) {
        $this->date = $date;
        $this->helper = $helper;
        $this->splitPayment = $splitPayment;
        $this->mailHelper = $mailHelper;
        $this->walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->walletTransactionFactory = $walletTransaction;
        $this->wallerRecordFactory = $walletRecord;
        $this->salesOrderFactory = $orderFactory;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * Sales order save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletTransaction = $this->walletTransactionFactory->create();
        $walletProductId = $this->helper->getWalletProductId();
        $orderId = $observer->getOrder()->getId();
        $order = $observer->getOrder();
        if ($order->getStatus() == 'complete') {
            $incrementId = $this->salesOrderFactory
                ->create()
                ->load($orderId)
                ->getIncrementId();
            $customerId = $order->getCustomerId();
            $currencyCode = $order->getOrderCurrencyCode();
            $currencyCreditAmount = $this->getCreditAmountData($orderId);
            if ($currencyCreditAmount > 0) {
                $baseCurrencyCode = $this->helper->getBaseCurrencyCode();
                $creditAmount = $this->helper->getwkconvertCurrency(
                    $currencyCode,
                    $baseCurrencyCode,
                    $currencyCreditAmount
                );
                $transferAmountData = [
                    'customer_id' => $customerId,
                    'walletamount' => $creditAmount,
                    'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                    'curr_code' => $currencyCode,
                    'curr_amount' => $currencyCreditAmount,
                    'walletnote' => __('Order id : %1 Cash Back Amount', $incrementId),
                    'sender_id' => 0,
                    'sender_type' => $walletTransaction::CASH_BACK_TYPE,
                    'order_id' => $orderId,
                    'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
                    'increment_id' => $incrementId
                ];
                $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
                $creditedAmountModel = $this->walletcreditAmountFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('order_id', $orderId);
                foreach ($creditedAmountModel as $model) {
                    $model->setStatus(1);
                    $this->splitPayment->commitMethod($model);
                }
            }
        }
    }

    /**
     * Get credit amount data
     *
     * @param int $orderId
     * @return void
     */
    public function getCreditAmountData($orderId)
    {
        $creditAmountModelClass = $this->walletcreditAmountFactory->create();
        $amount = 0;
        $creditAmountModel = $this->walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', $creditAmountModelClass::WALLET_CREDIT_AMOUNT_STATUS_DISABLE);

        if ($creditAmountModel->getSize()) {
            foreach ($creditAmountModel as $creditamount) {
                $amount = $creditamount->getAmount();
            }
        }
        return $amount;
    }
}
