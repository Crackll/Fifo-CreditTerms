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
use Magento\Sales\Model\Order;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Webkul\MpWalletSystem\Model\WalletrecordFactory;
use Webkul\MpWalletSystem\Model\WalletUpdateData;

/**
 * Webkul MpWalletSystem Observer Class
 */
class CreditMemoSaveAfter implements ObserverInterface
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
     * @var WallettransactionFactory
     */
    protected $walletTransaction;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletrecordFactory
     */
    protected $walletrecord;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletcreditamountFactory
     */
    protected $walletcreditAmountFactory;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;

    /**
     * Initialize dependencies
     *
     * @param OrderFactory                                           $orderFactory
     * @param \Magento\Sales\Model\Order\Invoice                     $invoice
     * @param \Webkul\MpWalletSystem\Helper\Mail                     $mailHelper
     * @param \Webkul\Marketplace\Model\OrdersFactory                $mpOrders
     * @param \Webkul\MpWalletSystem\Helper\Data                     $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime            $date
     * @param WallettransactionFactory                               $walletTransaction
     * @param \Webkul\MpWalletSystem\Model\AdminWallet               $adminWallet
     * @param WalletrecordFactory                                    $walletRecord
     * @param \Webkul\MpWalletSystem\Helper\SplitPayments            $splitPayment
     * @param \Webkul\Marketplace\Model\Saleslist                    $saleslist
     * @param \Webkul\MpWalletSystem\Logger\Logger                   $log
     * @param \Magento\Framework\Message\ManagerInterface            $messageManager
     * @param \Magento\Framework\App\Request\Http                    $request
     * @param \Magento\Customer\Model\Session                        $customerSession
     * @param \Webkul\MpWalletSystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
     * @param WalletUpdateData                                       $walletUpdateData
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrders,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        WallettransactionFactory $walletTransaction,
        \Webkul\MpWalletSystem\Model\AdminWallet $adminWallet,
        WalletrecordFactory $walletRecord,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayment,
        \Webkul\Marketplace\Model\Saleslist $saleslist,
        \Webkul\MpWalletSystem\Logger\Logger $log,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpWalletSystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        WalletUpdateData $walletUpdateData
    ) {
        $this->orderFactory = $orderFactory;
        $this->customerSession = $customerSession;
        $this->saleslist = $saleslist;
        $this->splitPayment = $splitPayment;
        $this->mpOrdersFactory = $mpOrders;
        $this->mailHelper = $mailHelper;
        $this->helper = $helper;
        $this->date = $date;
        $this->logger = $log;
        $this->walletTransaction = $walletTransaction;
        $this->walletrecord = $walletRecord;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->adminWallet = $adminWallet;
        $this->walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * Credit memo save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoiceId = $this->request->getParam('invoice_id');
        $loggedInId = $this->customerSession->getCustomer()->getId();
        $walletTransaction = $this->walletTransaction->create();
        $orderId = $observer->getEvent()->getCreditmemo()->getOrderId();
        $order = $this->orderFactory->create()->load($orderId);
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $params = $this->request->getParams();
            if (isset($params['creditmemo']['do_offline'])) {
                $doOffline = $params['creditmemo']['do_offline'];
                if ($doOffline == 0 && !$loggedInId) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(
                            "You can not do online refund for this order,
                             do the offline refund all the refunded amount will be transferred to customer's wallet"
                        )
                    );
                }
            }
            /**
             * Return if the refund is not from the invoice
             **/
            if (!$invoiceId) {
                return;
            }
            $mpOrdersCollection = $this->mpOrdersFactory->create()
                ->getCollection()
                ->addFieldToFilter('invoice_id', $invoiceId);
            if ($mpOrdersCollection->getSize()) {
                foreach ($mpOrdersCollection as $mpOrder) {
                    $sellerId = $mpOrder->getSellerId();
                }
            } else {
                $sellerId = 0;
            }
            
            $rowId = 0;
            $baserefundTotalAmount = 0;
            $refundTotalAmount = 0;
            $totalAmount = 0;
            $remainingAmount = 0;
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $baserefundTotalAmount = $creditmemo->getBaseGrandTotal();
            $refundTotalAmount = $creditmemo->getGrandTotal();
            $baserefundTotalAmount = $this->getDeductCashBackRefundAmount($baserefundTotalAmount, $orderId);
            $flag = 0;
            $walletProductId = $this->helper->getWalletProductId();
            $currencyCode = $order->getOrderCurrencyCode();
            $baseCurrencyCode = $this->helper->getBaseCurrencyCode();
            $refundTotalAmount = $this->helper->getwkconvertCurrency(
                $currencyCode,
                $baseCurrencyCode,
                $baserefundTotalAmount
            );

            $incrementId = $order->getIncrementId();
            $orderItem = $order->getAllItems();
            $productIdArray = [];
            foreach ($orderItem as $value) {
                $productIdArray[] = $value->getProductId();
            }
            if (!in_array($walletProductId, $productIdArray)) {
                try {
                    $this->updateWalletAmount(
                        $baserefundTotalAmount,
                        $refundTotalAmount,
                        $order,
                        $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                        $sellerId
                    );
                } catch (\Exception $e) {
                    $this->logger->addInfo($e->getMessage());
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(
                            "Something Went Wrong"
                        )
                    );
                }
            } else {
                $baserefundTotalAmount = $baserefundTotalAmount - $order->getDiscountAmount();
                $this->deductWalletAmountProduct($baserefundTotalAmount, $refundTotalAmount, $order);
            }
        }
    }

    /**
     * Deduct wallet amount from product
     *
     * @param int $baseAmount
     * @param int $amount
     * @param object $order
     * @return void
     */
    public function deductWalletAmountProduct($baseAmount, $amount, $order)
    {
        $walletTransaction = $this->walletTransaction->create();
        $currencyCode = $order->getOrderCurrencyCode();
        $orderId = $order->getId();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $remainingAmount = $this->helper->getWalletTotalAmount($customerId);
            if ($remainingAmount <= $baseAmount) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        "You can not generate credit memo of this order,
                        refunded amount is not available in customer's wallet"
                    )
                );
            } else {
                try {
                    $this->updateWalletAmount(
                        $baseAmount,
                        $amount,
                        $order,
                        $walletTransaction::WALLET_ACTION_TYPE_DEBIT
                    );
                } catch (\Exception $e) {
                    $this->logger->addInfo("Credit Memo Save After issue in update wallet amount method");
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(
                            "Something went wrong!"
                        )
                    );
                }
            }
        }
    }

    /**
     * Update wallet amount
     *
     * @param int $baserefundTotalAmount
     * @param int $refundTotalAmount
     * @param object $order
     * @param string $action
     * @return void
     */
    public function updateWalletAmount($baserefundTotalAmount, $refundTotalAmount, $order, $action, $sellerId)
    {
        $walletTransaction = $this->walletTransaction->create();
        $customerId = $order->getCustomerId();
        $currencyCode = $order->getOrderCurrencyCode();
        $orderId = $order->getId();
        $incrementId = $order->getIncrementId();

        $transferAmountData = [
            'customer_id' => $customerId,
            'walletamount' => $baserefundTotalAmount,
            'walletactiontype' => $action,
            'curr_code' => $currencyCode,
            'curr_amount' => $refundTotalAmount,
            'walletnote' => __('Order id : %1, %2ed amount', $incrementId, $action),
            'sender_id' => 0,
            'sender_type' => $walletTransaction::REFUND_TYPE,
            'order_id' => $orderId,
            'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => $incrementId
        ];
        if ($action==$walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
            //deduct amount from sellerId wallet
            if ($sellerId) {
                $sellerId;
                $orderId;
                $salesList = $this->saleslist->getCollection()
                    ->addFieldToFilter('seller_id', $sellerId)
                    ->addFieldToFilter('order_id', $orderId);
                $sellerAmount = 0;
                $totalCommission = 0;
                foreach ($salesList as $salesItem) {
                    $sellerAmount += $salesItem->getActualSellerAmount();
                    $totalCommission += $salesItem->getTotalCommission();
                }
                $action = $walletTransaction::WALLET_ACTION_TYPE_DEBIT;
                $transferAmountData = [
                    'customer_id' => $sellerId,
                    'walletamount' => $sellerAmount,
                    'walletactiontype' => $action,
                    'curr_code' => $currencyCode,
                    'curr_amount' => $sellerAmount,
                    'walletnote' => __('Refund for order id : %1, %2ed amount', $incrementId, $action),
                    'sender_id' => 0,
                    'sender_type' => $walletTransaction::REFUND_TYPE,
                    'order_id' => $orderId,
                    'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
                    'increment_id' => $incrementId
                ];
                $this->walletUpdateData->debitAmount($sellerId, $transferAmountData);
                if ($totalCommission) {
                    $type = $this->adminWallet::PAY_TYPE_ORDER_REFUND;
                    $this->adminWallet->updateInvoiceForAdmin($order, $totalCommission, $type, 1);
                }
            } else {
                $type = $this->adminWallet::PAY_TYPE_ORDER_REFUND;
                $this->adminWallet->updateInvoiceForAdmin($order, $baserefundTotalAmount, $type, 1);
            }
        } else {
            $this->walletUpdateData->debitAmount($customerId, $transferAmountData);
        }
    }

    /**
     * Get deduct cash back refund amount
     *
     * @param int $refundOrderAmount
     * @param int $orderId
     * @return void
     */
    public function getDeductCashBackRefundAmount($refundOrderAmount, $orderId)
    {
        $creditAmountModelClass = $this->walletcreditAmountFactory->create();
        $creditAmountModelCollection = $this->walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', $creditAmountModelClass::WALLET_CREDIT_AMOUNT_STATUS_DISABLE);
        if ($creditAmountModelCollection->getSize()) {
            foreach ($creditAmountModelCollection as $creditamount) {
                $rowId = $creditamount->getEntityId();
                $creditAmountModel = $this->loadCreditAmount($rowId);
                
                $amount = $creditAmountModel->getAmount();
                $creditAmountModel->setRefundAmount($amount);
                $creditAmountModel->setStatus($creditAmountModelClass::WALLET_CREDIT_AMOUNT_STATUS_ENABLE);
                $this->splitPayment->commitMethod($creditAmountModel);
            }
        } else {
            $refundAmount = 0;
            $amount = 0;
            $creditAmountModel = $this->walletcreditAmountFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('status', $creditAmountModelClass::WALLET_CREDIT_AMOUNT_STATUS_ENABLE);

            if ($creditAmountModel->getSize()) {
                foreach ($creditAmountModel as $creditamount) {
                    $refundAmount = $creditamount->getRefundAmount();
                    $amount = $creditamount->getAmount();
                }
            }
            if ($amount == $refundAmount) {
                return $refundOrderAmount;
            } else {
                $leftAmount = $amount - $refundAmount;
                if ($refundOrderAmount >= $leftAmount) {
                    $finalRefundAmount = $refundOrderAmount - $leftAmount;
                    $this->updateRefundAmount($leftAmount + $refundAmount, $orderId);
                    return $finalRefundAmount;
                } else {
                    $this->updateRefundAmount($refundOrderAmount + $refundAmount, $orderId);
                    return 0;
                }
            }
        }
        return $refundOrderAmount;
    }

    /**
     * Update refund amount
     *
     * @param int $amount
     * @param int $orderId
     * @return void
     */
    public function updateRefundAmount($amount, $orderId)
    {
        $creditAmount = $this->walletcreditAmountFactory->create();
        $creditAmountModel = $this->walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', $creditAmount::WALLET_CREDIT_AMOUNT_STATUS_ENABLE);
        if ($creditAmountModel->getSize()) {
            foreach ($creditAmountModel as $amountModel) {
                $amountModel->setRefundAmount($amount);
                $this->splitPayment->commitMethod($amountModel);
            }
        }
    }

    /**
     * Load Credit Amount collection
     *
     * @param int $rowId
     *
     * @return collection
     */
    public function loadCreditAmount($rowId)
    {
        return $this->walletcreditAmountFactory->create()
            ->load($rowId);
    }
}
