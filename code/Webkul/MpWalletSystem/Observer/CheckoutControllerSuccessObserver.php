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
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\OrderFactory;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Webkul\MpWalletSystem\Model\WalletrecordFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Webkul\MpWalletSystem\Model\WalletUpdateData;

/**
 * Webkul MpWalletSystem Observer Class
 */
class CheckoutControllerSuccessObserver implements ObserverInterface
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;
    
    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletcreditamountFactory
     */
    protected $walletcreditAmountFactory;
    
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $orderModel;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WallettransactionFactory
     */
    protected $walletTransaction;
    
    /**
     * @var WalletrecordFactory
     */
    protected $walletRecordFactory;
    
    /**
     * @var Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    
    /**
     * @var Magento\Framework\DB\Transaction
     */
    protected $dbTransaction;
    
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
     * @param \Webkul\Marketplace\Model\Saleslist                    $saleslist
     * @param \Webkul\MpWalletSystem\Logger\Logger                   $logger
     * @param \Webkul\Marketplace\Model\Product                      $mpProduct
     * @param \Magento\Checkout\Model\Session                        $checkoutSession
     * @param \Magento\Catalog\Model\Product                         $productFactory
     * @param \Webkul\Marketplace\Helper\Orders                      $mpOrdersHelper
     * @param \Webkul\MpWalletSystem\Helper\SplitPayments            $splitPayment
     * @param \Webkul\Marketplace\Model\OrdersFactory                $mpOrderFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface   $stockRegistry
     * @param InvoiceSender                                          $invoiceSender
     * @param \Webkul\MpWalletSystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
     * @param OrderFactory                                           $orderModel
     * @param WallettransactionFactory                               $walletTransaction
     * @param WalletrecordFactory                                    $walletRecordModel
     * @param InvoiceService                                         $invoiceService
     * @param Transaction                                            $dbTransaction
     * @param WalletUpdateData                                       $walletUpdateData
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        \Webkul\Marketplace\Model\Saleslist $saleslist,
        \Webkul\MpWalletSystem\Logger\Logger $logger,
        \Webkul\Marketplace\Model\Product $mpProduct,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product $productFactory,
        \Webkul\Marketplace\Helper\Orders $mpOrdersHelper,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayment,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrderFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        InvoiceSender $invoiceSender,
        \Webkul\MpWalletSystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        OrderFactory $orderModel,
        WallettransactionFactory $walletTransaction,
        WalletrecordFactory $walletRecordModel,
        InvoiceService $invoiceService,
        Transaction $dbTransaction,
        WalletUpdateData $walletUpdateData
    ) {
        $this->splitPayment = $splitPayment;
        $this->date = $date;
        $this->helper = $helper;
        $this->mailHelper = $mailHelper;
        $this->mpOrdersHelper = $mpOrdersHelper;
        $this->mpOrderFactory = $mpOrderFactory;
        $this->mpProduct = $mpProduct;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->checkoutSession = $checkoutSession;
        $this->stockRegistry = $stockRegistry;
        $this->invoiceSender = $invoiceSender;
        $this->walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->orderModel = $orderModel;
        $this->walletTransaction = $walletTransaction;
        $this->walletRecordFactory = $walletRecordModel;
        $this->invoiceService = $invoiceService;
        $this->saleslist = $saleslist;
        $this->dbTransaction = $dbTransaction;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * Sales order place after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $walletTransaction = $this->walletTransaction->create();
            $orderIds = $observer->getOrderIds();
            foreach ($orderIds as $orderId) {
                $walletProductId = $this->helper->getWalletProductId();
                $order = $this->getLoadOrderById($orderId);
                if ($this->alreadyAddedInData($order)) {
                    continue;
                }
                $customerId = $order->getCustomerId();
                $currencyCode = $order->getOrderCurrencyCode();
                $incrementId = $order->getIncrementId();
                $flag = 0;
                if ($orderId) {
                    foreach ($order->getAllVisibleItems() as $item) {
                        $productId = $item->getProductId();
                        if ($productId == $walletProductId) {
                            $price = number_format($item->getBasePrice(), 2, '.', '');
                            $currPrice = number_format($item->getPrice(), 2, '.', '');
                            $flag = 1;
                        }
                    }
                }
                $totalAmount = 0;
                $usedAmount = 0;
                $remainingAmount = 0;
                if ($flag == 1) {
                    //for wallet recharge orders
                    $transferAmountData = [
                        'customer_id' => $customerId,
                        'walletamount' => $price,
                        'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                        'curr_code' => $currencyCode,
                        'curr_amount' => $currPrice,
                        'walletnote' => __('Order id : %1 credited amount', $incrementId),
                        'sender_id' => $customerId,
                        'sender_type' => $walletTransaction::ORDER_PLACE_TYPE,
                        'order_id' => $orderId,
                        'status' => $walletTransaction::WALLET_TRANS_STATE_PENDING,
                        'increment_id' => $incrementId
                    ];
                    $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
                    $this->mailHelper->checkAndUpdateWalletAmount($order);
                } else {
                    //for orders other then wallet recharge
                    $discountAmount = $order->getBaseWalletAmount();
                    $discountcurrAmount = $order->getWalletAmount();
                    $walletDiscountParams = [];
                    if ($this->checkoutSession->getWalletDiscount()) {
                        $walletDiscountParams = $this->checkoutSession->getWalletDiscount();
                    }
                    if (array_key_exists('flag', $walletDiscountParams) && $walletDiscountParams['flag'] == 1) {
                        $transferAmountData = [
                            'customer_id' => $customerId,
                            'walletamount' => -1 * $discountAmount,
                            'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_DEBIT,
                            'curr_code' => $currencyCode,
                            'curr_amount' => -1 * $discountcurrAmount,
                            'walletnote' => __('Order id : %1 debited amount', $incrementId),
                            'sender_id' => $customerId,
                            'sender_type' => $walletTransaction::ORDER_PLACE_TYPE,
                            'order_id' => $orderId,
                            'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
                            'increment_id' => $incrementId
                        ];
                        $this->walletUpdateData->debitAmount($customerId, $transferAmountData);
                    }
                    $this->addCreditAmountData($orderId);
                    //generate invoice automatically if whole amount is paid by wallet
                    $this->checkPaymentMethod($order);
                    /**
                    * @todo count sellers in order
                    * Get products from marketplacesaleslist table sorted as per the actual_seller_amount
                    * Distribute wallet amount used
                    **/
                }
                $this->updateWaletProductQuantity($walletProductId);
            }
            $this->checkoutSession->unsWalletDiscount();
        } catch (\Exception $e) {
            $this->logger->addInfo($e->getMessage());
        }
    }

    /**
     * Check Payment Method function
     *
     * @param object $order
     */
    public function checkPaymentMethod($order)
    {
        if ($order->getPayment()->getMethod() == 'mpwalletsystem') {
            $order = $this->updateOrder($order);
            $order->setWalletInvoiced(1);
            $this->splitPayment->commitMethod($order);
            $this->generateInvoiceForWalletPayment($order);
        }
    }

    /**
     * Get Load Order By Id function
     *
     * @param int $orderId
     * @return object $order
     */
    protected function getLoadOrderById($orderId)
    {
        $order = $this->orderModel
            ->create()
            ->load($orderId);
        return $order;
    }

    /**
     * Update Walet Product Quantity function
     *
     * @param int $walletProductId
     */
    public function updateWaletProductQuantity($walletProductId)
    {
        $product = $this->productFactory->load($walletProductId); //load product which you want to update stock
        $stockItem = $this->stockRegistry->getStockItem($walletProductId); // load stock of that product
        $stockItem->setData('manage_stock', 0);
        $stockItem->setData('use_config_notify_stock_qty', 0);
        $stockItem->save(); //save stock of item
        $this->stockRegistry->updateStockItemBySku($this->helper::WALLET_PRODUCT_SKU, $stockItem);
        $product->setStockData(
            [
                'use_config_manage_stock' => 0,
                'manage_stock' => 0
            ]
        )->save(); //  also save product
    }

    /**
     * Add Credit Amount Data function
     *
     * @param int $orderId
     */
    public function addCreditAmountData($orderId)
    {
        $creditamount = $this->helper->calculateCreditAmountforCart();
        if ($creditamount > 0) {
            $status = $this->checkAlreadyAdded($orderId);
            if ($status) {
                $creditAmountModel = $this->walletcreditAmountFactory->create();
                $creditAmountModel->setAmount($creditamount)
                    ->setOrderId($orderId)
                    ->setStatus($creditAmountModel::WALLET_CREDIT_AMOUNT_STATUS_DISABLE)
                    ->save();
            }
        }
    }

    /**
     * Check Already Added function
     *
     * @param int $orderId
     */
    public function checkAlreadyAdded($orderId)
    {
        $creditAmountCollection = $this->walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId);
        if ($creditAmountCollection->getSize()) {
            return false;
        }
        return true;
    }

    /**
     * Generate invoice for wallet payment
     *
     * @param order $order
     * @return void
     */
    public function generateInvoiceForWalletPayment($order)
    {
        $this->logger->addInfo('invoice started');
        /**
         * Will be called only in case of full payments using wallets
         * Get products from marketplacesaleslist table sorted as per the  actual_seller_amount
         * Distribute wallet amount used
         **/
        if ($order->canInvoice()) {
            $walletAmount = $order->getWalletAmount();
            $sellerDistributedAmountArr = [];
            $sellerAmountCollection = $this->saleslist->getCollection()
                ->addFieldToFilter('order_id', $order->getId())
                ->setOrder('actual_seller_amount', 'ASC');
            foreach ($sellerAmountCollection as $sellerAmount) {
                $sellerId = $sellerAmount->getSellerId();
                if (isset($sellerDistributedAmountArr[$sellerId])) {
                    $sellerDistributedAmountArr[$sellerId] += $sellerAmount->getActualSellerAmount();
                } else {
                    $sellerDistributedAmountArr[$sellerId] = $sellerAmount->getActualSellerAmount();
                }
            }
            $amountDistributed = 0;
            foreach ($sellerDistributedAmountArr as $sellerId => $amount) {
                $this->logger->addInfo("transfer for seller ".$sellerId.' amount'.$amount);
                if ($sellerId > 0) {
                    $amountDistributed += $amount;
                    continue;
                }
                $transferData = [
                    'walletamount' => $amount,
                    'walletactiontype' => "credit",
                    'walletnote' => __("Amount For Order")." ".$order->getId(),
                    'curr_code' => $order->getOrderCurrencyCode(),
                    'curr_amount' => $amount,
                    'sender_id' => 0,
                    'sender_type' => 3,
                    'order_id' => $order->getId(),
                    'status' => 1,
                    'increment_id' => $order->getIncrementId()
                ];
                $this->walletUpdateData->creditAmount($sellerId, $transferData);
            }

            $remainingAmount = $order->getGrandTotal() - $amountDistributed;
            $getSellerWiseProductsFromOrder = $this->getSellerWiseProductsFromOrder($order);

            $transactionId = "tempTxn";
            foreach ($getSellerWiseProductsFromOrder as $sellerId => $sellerData) {
                //generate transaction
                $this->dataHelper->createTransaction($order, $sellerDistributedAmountArr[$sellerId]);
                //generate transaction
                $invoice = $this->invoiceService
                    ->prepareInvoice($order, $sellerData);
                $invoice->setTransactionId($transactionId);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->setShippingAmount(0);
                $invoice->setSubtotal($sellerDistributedAmountArr[$sellerId]);
                $invoice->setBaseSubtotal($sellerDistributedAmountArr[$sellerId]);
                $invoice->setGrandTotal($sellerDistributedAmountArr[$sellerId]);

                $invoice->setBaseGrandTotal($sellerDistributedAmountArr[$sellerId]);
                $invoice->register();
                $this->splitPayment->commitMethod($invoice);
                $invoice->getOrder()->setIsInProcess(true);
                $transactionSave = $this->dbTransaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $this->splitPayment->commitMethod($transactionSave);
                $this->invoiceSender->send($invoice);
                //send notification code
                $order->addStatusHistoryComment(
                    __(
                        'Notified customer about invoice #%1.',
                        $invoice->getId()
                    )
                )
                    ->setIsCustomerNotified(true)
                    ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $this->splitPayment->commitMethod($order);
                $mpTracking = $this->mpOrderFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('order_id', $order->getId())
                    ->addFieldToFilter('seller_id', $sellerId);

                if ($mpTracking->getSize() > 0) {
                    foreach ($mpTracking as $row) {
                        $row->setInvoiceId($invoice->getId());
                        $this->splitPayment->commitMethod($row);
                    }
                }
                try {
                    $this->mpOrdersHelper->paysellerpayment($order, $sellerId, $invoice->getTransactionId());
                } catch (\Exception $e) {
                    $this->logger->addInfo($e->getMessage());
                }
            }
        }
    }

    /**
     * Already added in data
     *
     * @param object $order
     * @return bool
     */
    public function alreadyAddedInData($order)
    {
        $transactionCollection = $this->walletTransaction
            ->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId());

        if ($transactionCollection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Update order
     *
     * @param object $order
     * @return void
     */
    public function updateOrder($order)
    {
        $order->setGrandTotal($order->getWalletAmount());
        $order->save();
        return $order;
    }

    /**
     * Get Seller Wise Products From Order
     *
     * @param object $order
     * @return array
     */
    public function getSellerWiseProductsFromOrder($order)
    {
        $sellerWiseProductArr = [];
        $orderItemsCollection = $order->getAllItems();
        foreach ($orderItemsCollection as $item) {
            $mpProductCollection = $this->mpProduct->getCollection();
            $productDataColl = $mpProductCollection->addFieldToFilter('mageproduct_id', $item->getProductId());
            if ($productDataColl->getSize()) {
                foreach ($productDataColl as $productData) {
                    $sellerWiseProductArr[$productData->getSellerId()][$item->getId()] = $item->getQtyOrdered();
                }
            } else {
                $sellerWiseProductArr[0][$item->getId()] = $item->getQtyOrdered();
            }
        }
        return $sellerWiseProductArr;
    }
}
