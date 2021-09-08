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

namespace Webkul\MpWalletSystem\Helper;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\OrderFactory;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Webkul\MpWalletSystem\Model\WalletrecordFactory;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Session\SessionManager;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Webkul MpWalletSystem Helper Class
 */
class SplitPayments extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime            $date
     * @param \Webkul\MpWalletSystem\Helper\Mail                     $mailHelper
     * @param \Magento\Checkout\Model\Session                        $checkoutSession
     * @param \Magento\Catalog\Model\Product                         $productFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface   $stockRegistry
     * @param InvoiceSender                                          $invoiceSender
     * @param \Webkul\MpWalletSystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
     * @param \Webkul\MpWalletSystem\Model\AdminWallet               $adminWallet
     * @param OrderFactory                                           $orderModel
     * @param \Webkul\Marketplace\Model\Saleslist                    $saleslist
     * @param WallettransactionFactory                               $walletTransaction
     * @param \Webkul\Marketplace\Model\Product                      $mpProduct
     * @param WalletrecordFactory                                    $walletRecordModel
     * @param \Webkul\MpWalletSystem\Helper\Data                     $dataHelper
     * @param PaymentTransaction\BuilderInterface                    $transactionBuilder
     * @param InvoiceService                                         $invoiceService
     * @param \Webkul\Marketplace\Model\OrdersFactory                $mpOrderFactory
     * @param \Webkul\MpWalletSystem\Logger\Logger                   $log
     * @param Transaction                                            $dbTransaction
     * @param WalletUpdateData                                       $walletUpdateData
     * @param QuoteRepository                                        $quoteRepository
     * @param \Webkul\Marketplace\Helper\Orders                      $mpOrdersHelper
     * @param SessionManager                                         $coreSession
     * @param OrderRepositoryInterface                               $orderRepository
     * @param \Magento\Framework\App\Helper\Context                  $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product $productFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        InvoiceSender $invoiceSender,
        \Webkul\MpWalletSystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        \Webkul\MpWalletSystem\Model\AdminWallet $adminWallet,
        OrderFactory $orderModel,
        \Webkul\Marketplace\Model\Saleslist $saleslist,
        WallettransactionFactory $walletTransaction,
        \Webkul\Marketplace\Model\Product $mpProduct,
        WalletrecordFactory $walletRecordModel,
        \Webkul\MpWalletSystem\Helper\Data $dataHelper,
        PaymentTransaction\BuilderInterface $transactionBuilder,
        InvoiceService $invoiceService,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrderFactory,
        \Webkul\MpWalletSystem\Logger\Logger $log,
        Transaction $dbTransaction,
        WalletUpdateData $walletUpdateData,
        QuoteRepository $quoteRepository,
        \Webkul\Marketplace\Helper\Orders $mpOrdersHelper,
        SessionManager $coreSession,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->date = $date;
        $this->logger = $log;
        $this->mailHelper = $mailHelper;
        $this->productFactory = $productFactory;
        $this->checkoutSession = $checkoutSession;
        $this->stockRegistry = $stockRegistry;
        $this->transactionBuilder = $transactionBuilder;
        $this->invoiceSender = $invoiceSender;
        $this->walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->orderModel = $orderModel;
        $this->dataHelper = $dataHelper;
        $this->mpProduct = $mpProduct;
        $this->mpOrderFactory = $mpOrderFactory;
        $this->walletTransaction = $walletTransaction;
        $this->walletRecordFactory = $walletRecordModel;
        $this->invoiceService = $invoiceService;
        $this->mpOrdersHelper = $mpOrdersHelper;
        $this->dbTransaction = $dbTransaction;
        $this->walletUpdateData = $walletUpdateData;
        $this->quoteRepository = $quoteRepository;
        $this->saleslist = $saleslist;
        $this->coreSession = $coreSession;
        $this->adminWallet = $adminWallet;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * Set Data of wallet transaction
     *
     * @param int $orderId
     * @param object $order
     */
    public function setDataInWalletTable($orderId, $order)
    {
        $walletTransaction = $this->walletTransaction->create();
        $walletProductId = $this->dataHelper->getWalletProductId();
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
        } else {
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
            if ($order->getPayment()->getMethod() == 'mpwalletsystem') {
                    $order = $this->updateOrder($order);
                    $order->setWalletInvoiced(1);
                    $order->save();
                    $this->generateInvoiceForWalletPayment($order);
                    $this->startWalletDistribution($order);
            } else {
                /**
                 * Will execute in case of other payment methods
                 * Take wallet amount from Magetno sales order table
                 * Distribute wallet amount in marketplace_sales order table
                 **/
                $walletAmount = -$order->getWalletAmount();
                //check if wallet has been applied to the order
                if ($walletAmount) {
                    try {
                        $this->startWalletDistribution($order);
                    } catch (\Exception $e) {
                        $this->logger->addInfo("error in setDataInWalletTable method ".$e->getMessage());
                    }
                }
            }
        }
        $this->updateWaletProductQuantity($walletProductId);
    }

    /**
     * Add Credit Amount Data function
     *
     * @param int $orderId
     */
    public function addCreditAmountData($orderId)
    {
        $creditamount = $this->dataHelper->calculateCreditAmountforCart($orderId);
        if ($creditamount > 0) {
            $creditAmountModel = $this->walletcreditAmountFactory->create();
            $creditAmountModel->setAmount($creditamount)
                ->setOrderId($orderId)
                ->setStatus($creditAmountModel::WALLET_CREDIT_AMOUNT_STATUS_DISABLE)
                ->save();
        }
    }

    /**
     * Will be called only in case of full payments using wallets
     * Get products from marketplacesaleslist table sorted as per the  actual_seller_amount
     * Distribute wallet amount used
     *
     * @param object $order
     **/
    public function generateInvoiceForWalletPayment($order)
    {
        $walletAmount = -$order->getWalletAmount();
        $sellerAmountCollection = $this->prepareSellerAmountCollection($order);
        $sellerDistributedAmountArr = $this->getSellerDistributedAmountArr($sellerAmountCollection, $order);
        $distributedWalletAmountArr = $this->prepareSellerWiseWalletData(
            $sellerDistributedAmountArr,
            $walletAmount,
            $order
        );
        $amountDistributed = 0;
        foreach ($sellerDistributedAmountArr as $sellerId => $amount) {
            if ($sellerId == 0) {
                continue;
            }
            $transferData = [
                'customer_id' => $sellerId,
                'walletamount' => $amount['sellerAmount'] + $amount['shippingAmount'] + $amount['tax'],
                'walletactiontype' => "credit",
                'walletnote' => __("Amount For Order")." ".$order->getIncrementId(),
                'curr_code' => $order->getOrderCurrencyCode(),
                'curr_amount' => $amount['sellerAmount'] + $amount['shippingAmount'] + $amount['tax'],
                'sender_id' => 0,
                'sender_type' => 3,
                'order_id' => $order->getId(),
                'status' => 1,
                'increment_id' => $order->getIncrementId()
            ];
            try {
                $this->walletUpdateData->creditAmount($sellerId, $transferData);
            } catch (\Exception $e) {
                $this->logger->addInfo("Error transfer amount ".$e->getMessage());
            }
        }
        $shippingAmount = $order->getBaseShippingAmount();
        $getSellerWiseProductsFromOrder = $this->getSellerWiseProductsFromOrder($order);
        if ($shippingAmount && !isset($getSellerWiseProductsFromOrder[0])) {
            $getSellerWiseProductsFromOrder[0] = [0=>0];
            $sellerDistributedAmountArr[0] = [
                                                'shippingAmount'=>$shippingAmount,
                                                'totalamount' => 0,
                                                'tax'=>0
                                            ];
        }
        foreach ($getSellerWiseProductsFromOrder as $sellerId => $sellerData) {
            //generate transaction
            $transactionId = $this->dataHelper->createTransaction(
                $order,
                $sellerDistributedAmountArr[$sellerId]['shippingAmount'] +
                $sellerDistributedAmountArr[$sellerId]['totalamount']
            );
            //generate transaction
            $invoice = $this->invoiceService
                ->prepareInvoice($order, $sellerData);
            $invoice->setTransactionId($transactionId);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->setShippingAmount($sellerDistributedAmountArr[$sellerId]['shippingAmount']);
            $invoice->setSubtotal($sellerDistributedAmountArr[$sellerId]['totalamount']);
            $invoice->setBaseSubtotal($sellerDistributedAmountArr[$sellerId]['totalamount']);
            $invoice->setGrandTotal(
                $sellerDistributedAmountArr[$sellerId]['totalamount'] +
                $sellerDistributedAmountArr[$sellerId]['shippingAmount'] +
                $sellerDistributedAmountArr[$sellerId]['tax']
            );
            $invoice->setBaseGrandTotal(
                $sellerDistributedAmountArr[$sellerId]['totalamount'] +
                $sellerDistributedAmountArr[$sellerId]['shippingAmount'] +
                $sellerDistributedAmountArr[$sellerId]['tax']
            );
            $invoice->register();
            $this->commitMethod($invoice);
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = $this->dbTransaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $this->commitMethod($transactionSave);
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
            $this->commitMethod($order);
            $mpTracking = $this->mpOrderFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('seller_id', $sellerId);

            if ($mpTracking->getSize() > 0) {
                foreach ($mpTracking as $row) {
                    $row->setInvoiceId($invoice->getId());
                    $this->commitMethod($row);
                }
            }
            //for payout at marketplace
            $this->updateSalesList($order);
            try {
                $this->mpOrdersHelper->paysellerpayment($order, $sellerId, $transactionId);
            } catch (\Exception $e) {
                $this->logger->addInfo($e->getMessage());
            }
        }
    }

    /**
     * Check if transaction Already Added
     *
     * @param object $order
     * @return boolean
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
        $this->stockRegistry->updateStockItemBySku($this->dataHelper::WALLET_PRODUCT_SKU, $stockItem);
        $product->setStockData(
            [
                'use_config_manage_stock' => 0,
                'manage_stock' => 0
            ]
        )->save(); //  also save product
    }

    /**
     * Update Order function
     *
     * @param object $order
     * @return object
     */
    public function updateOrder($order)
    {
        $order->setBaseTotal(-$order->getWalletAmount());
        $order->setBaseGrandTotal(-$order->getWalletAmount());
        $order->setGrandTotal(-$order->getWalletAmount());
        $order->save();
        return $order;
    }

    /**
     * Creates transactions for payments
     *
     * @param  \Mageto\Sales\Model\Order $order
     * @param  int                       $amount
     * @return string $transId
     **/
    public function createTransaction($order, $amount)
    {
        try {
            $transId = 'wk_wallet_system'.$order->getId().'-'.rand();
            $transArray = [
                'id' => $transId,
                'amount' => $amount
            ];

            $payment = $order->getPayment();
            $payment->setLastTransId($transArray['id']);
            $payment->setTransactionId($transArray['id']);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $transArray]
            );
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $amount
            );

            $message = __('The authorized amount is %1.', $formatedPrice);

            $trans = $this->transactionBuilder;
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transArray['id'])
                ->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $transArray]
                )
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
            return $transId;
        } catch (\Exception $e) {
            $this->logger->addInfo($e->getMessage());
        }
    }

    /**
     * Prepare seller wise products from order
     * $sellerId[$productId=>$quantity]
     *
     * @param  \Magento\Sales\Model\Order
     * @return array $sellerWiseProductArr
     **/
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

    /**
     * Returns data from marketplace_sales table and marketplacesaleslist table
     *
     * @param  \Magento\Sales\Model\Order
     * @return collection
     */
    public function prepareSellerAmountCollection($order)
    {
        try {
            $sellerAmountCollection = $this->saleslist->getCollection();
            $marketplaceOrdersTable = $sellerAmountCollection->getTable('marketplace_orders');
            $sellerAmountCollection->addFieldToFilter('main_table.order_id', $order->getId())
                ->addFieldToFilter('main_table.total_amount', ['neq'=>'0'])
                ->setOrder('actual_seller_amount', 'ASC')
                ->getSelect()
                ->joinLeft(
                    $marketplaceOrdersTable.' as mo',
                    'main_table.order_id = mo.order_id AND main_table.seller_id = mo.seller_id',
                    [
                        'product_ids'=>'product_ids',
                        'shipment_id'=>'shipment_id',
                        'invoice_id'=>'invoice_id',
                        'creditmemo_id'=>'creditmemo_id',
                        'is_canceled'=>'is_canceled',
                        'shipping_charges'=>'shipping_charges',
                        'carrier_name'=>'carrier_name',
                        'tracking_number'=>'tracking_number',
                        'tax_to_seller'=>'tax_to_seller',
                        'coupon_amount'=>'coupon_amount',
                        'refunded_coupon_amount'=>'refunded_coupon_amount',
                        'refunded_shipping_charges'=>'refunded_shipping_charges',
                        'seller_pending_notification'=>'seller_pending_notification',
                        'order_status'=>'order_status',
                        'wallet_invoiced'=>'wallet_invoiced',
                        'wallet_amount'=>'wallet_amount',
                    ]
                );
            return $dataCollection = $sellerAmountCollection;
        } catch (\Exception $e) {
            $this->logger->addInfo($e->getMessage());
        }
    }

    /**
     * Update Sales order
     *
     * @param object $order
     */
    public function updateSalesList($order)
    {
        $salesCollection =  $this->saleslist->getCollection()
            ->addFieldtoFilter('order_id', $order->getId());

        foreach ($salesCollection as $order) {
            $order->setCpprostatus(1);
            $this->commitMethod($order);
        }
    }

    /**
     * Get Seller Distributed Amount Array
     *
     * @param object $sellerAmountCollection
     * @param object $order
     * @return array
     */
    public function getSellerDistributedAmountArr($sellerAmountCollection, $order)
    {
        $adminAmount = 0;
        $shippingAmount = $order->getBaseShippingAmount();
        foreach ($sellerAmountCollection as $sellerAmount) {
            $sellerId = $sellerAmount->getSellerId();
            if ($sellerId == 0) {
                $adminAmount += $sellerAmount->getActualSellerAmount() +
                                $sellerAmount->getTotalCommission() +
                                $sellerAmount->getShippingCharges();
            }
            $adminAmount += $sellerAmount->getTotalCommission();
            if (isset($sellerDistributedAmountArr[$sellerId])) {
                $sellerDistributedAmountArr[$sellerId]['sellerAmount'] += $sellerAmount->getActualSellerAmount();
                $sellerDistributedAmountArr[$sellerId]['shippingAmount'] += $sellerAmount->getShippingCharges();
                $sellerDistributedAmountArr[$sellerId]['totalamount'] += $sellerAmount->getTotalAmount();
                $sellerDistributedAmountArr[$sellerId]['tax'] += $sellerAmount->getTotalTax();
                $shippingAmount -= $sellerAmount->getShippingCharges();
            } else {
                $sellerDistributedAmountArr[$sellerId]['sellerAmount'] = $sellerAmount->getActualSellerAmount();
                $sellerDistributedAmountArr[$sellerId]['shippingAmount'] = $sellerAmount->getShippingCharges();
                $sellerDistributedAmountArr[$sellerId]['totalamount'] = $sellerAmount->getTotalAmount();
                $sellerDistributedAmountArr[$sellerId]['tax'] = $sellerAmount->getTotalTax();
                $shippingAmount -= $sellerAmount->getShippingCharges();
            }
        }
        /**
         * $adminAmount is the total amount to be transferred in admin wallet
         * Check if no product fo admin but shipping is there
         **/
        $adminShippingOnly = $this->checkAdminShippingOnly($sellerAmountCollection, $order);
        if ($adminShippingOnly) {
            $sellerDistributedAmountArr[0]['shippingAmount'] = $order->getBaseShippingAmount();
            $sellerDistributedAmountArr[0]['totalamount'] = 0;
            $sellerDistributedAmountArr[0]['tax'] = 0;
        }
        return $sellerDistributedAmountArr;
    }

    /**
     * Prepare seller wise data to distribute wallet amount in case of partial wallet payment
     *
     * @param array                      $sellerDistributedAmountArr
     * @param int                        $walletAmount
     * @param \Magento\Model\Sales\Order $order
     */
    public function prepareSellerWiseWalletData($sellerDistributedAmountArr, $walletAmount, $order)
    {
         $walletCreditData = [];
        foreach ($sellerDistributedAmountArr as $sellerId => $sellerData) {
            if (!$sellerId) {
                $sellerTotal = $sellerData['totalamount'] + $sellerData['shippingAmount'] + $sellerData['tax'];
            } else {
                $sellerTotal = $sellerData['sellerAmount'] + $sellerData['shippingAmount']+ $sellerData['tax'];
            }
            $amountPercent = $sellerTotal/$order->getGrandTotal();
            $walletForSeller = $walletAmount*$amountPercent;
            $walletCreditData[$sellerId] = $walletForSeller;
        }
        return $walletCreditData;
    }

    /**
     * Enters the wallet distributed data in marketplace_orders table
     *
     * @param object $distributedWalletAmountArr
     * @param object $order
     */
    public function startDistributionData($distributedWalletAmountArr, $order)
    {
        $totalDistributedAmount = 0;
        $walletAmount = -$order->getWalletAmount();
        foreach ($distributedWalletAmountArr as $sellerId => $amount) {
            $mpTracking = $this->mpOrderFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('seller_id', $sellerId);
            foreach ($mpTracking as $sellerOrder) {
                if ($sellerId) {
                    $sellerOrder->setWalletAmount($amount);
                    $this->commitMethod($sellerOrder);
                    $totalDistributedAmount = $totalDistributedAmount + $amount;
                }
            }
        }
        $remainingAmount = $walletAmount - $totalDistributedAmount;
        $type = $this->adminWallet::PAY_TYPE_ORDER_AMOUNT;
        $this->adminWallet->updateInvoiceForAdmin($order, $remainingAmount, $type, 1);
    }

    /**
     * Checks if there is no admin product but there is admin shipping
     *
     * @param object $sellerAmountCollection
     * @param object $order
     * @return boolean
     */
    public function checkAdminShippingOnly($sellerAmountCollection, $order)
    {
        $sellerTotal = 0;
        foreach ($sellerAmountCollection as $sellerData) {
            $sellerTotal += $sellerData->getTotalAmount() + $sellerData->getShippingCharges();
        }
        if ($sellerTotal < $order->getGrandTotal()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generate two invoices with 0 quantities
     * First for wallet amount $amount
     * Second for remaining shipping amount in order
     *
     * @param int $amount
     * @param object $order
     */
    public function generateShippingInvoices($amount, $order)
    {
        $invoiceId = $this->generateShippingInvoice($order, $amount, []);
        $type = $this->adminWallet::PAY_TYPE_SHIPPING;
        $this->adminWallet->updateInvoiceForAdmin($order, $amount, $type, $invoiceId);
        $this->generateShippingInvoice($order, $order->getShippingAmount() - $amount, []);
    }

    /**
     * Generate Shipping Invoice
     *
     * @param object $order
     * @param int $amount
     * @param int $quantity
     * @return int
     */
    public function generateShippingInvoice($order, $amount, $quantity)
    {
        $transactionId = $this->dataHelper->createTransaction($order, $amount);
        //generate transaction
        $invoice = $this->invoiceService
            ->prepareInvoice($order, $quantity);
        $invoice->setTransactionId($transactionId);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->setShippingAmount($amount);
        $invoice->setSubtotal(0);
        $invoice->setBaseSubtotal(0);
        $invoice->setGrandTotal($amount);
        $invoice->setBaseGrandTotal($amount);
        $invoice->register();
        $invoice->save();
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = $this->dbTransaction
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transactionSave->save();
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
            ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->save();
        return $invoice->getId();
    }

    /**
     * Check if shipping invoice is created for admin
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function checkIfShippingInvoiceAlreadyCreated($order)
    {
        $type = \Webkul\MpWalletSystem\Model\AdminWallet::PAY_TYPE_SHIPPING;
        return $this->adminWallet->checkInvoiceCreatedForOrder($order, $type);
    }

    /**
     * Checks if the each seller in the order has been invoiced
     * Checks if there is admin shipping applied on theoprder
     * Creates 2 invoices for shipping
     * First for the wallet amount
     * Second for the remaining shipping amount
     *
     * @param object order
     */
    public function checkAndCreateShippingInvoices($order)
    {
        if (!$this->checkIfShippingInvoiceAlreadyCreated($order)) {
            $mpOrderCollection = $this->mpOrderFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('invoice_id', ['eq'=>0]);
            if (!$mpOrderCollection->getSize()) {
                /**
                * No admin items in marketplace_orders table
                * Check for shipping applied only
                **/
                $sellerAmountCollection = $this->prepareSellerAmountCollection($order);
                
                $adminShippingOnly = $this->checkAdminShippingOnly($sellerAmountCollection, $order);
                if ($adminShippingOnly) {
                    $walletAmount = -$order->getWalletAmount();
                    $sellerDistributedAmountArr = $this->getSellerDistributedAmountArr($sellerAmountCollection, $order);
                    //calculate commission and shipping
                    $distributedWalletAmountArr = $this->prepareSellerWiseWalletData(
                        $sellerDistributedAmountArr,
                        $walletAmount,
                        $order
                    );
                    $amount = $distributedWalletAmountArr[0];
                    $type = \Webkul\MpWalletSystem\Model\AdminWallet::PAY_TYPE_SHIPPING;
                    $this->adminWallet->updateInvoiceForAdmin($order, $amount, $type, 1);
                    $this->generateShippingInvoices($amount, $order);
                }
            }
        }
    }

    /**
     * Start Wallet Distribution between seller and admin
     *
     * @param object order
     */
    public function startWalletDistribution($order)
    {
        $walletAmount = -$order->getWalletAmount();
        // seller amount collection from order
        $sellerAmountCollection = $this->prepareSellerAmountCollection($order);
        $adminShipping = $this->checkAdminShippingOnly($sellerAmountCollection, $order);
        // prepare distributed price array
        $sellerDistributedAmountArr = $this->getSellerDistributedAmountArr($sellerAmountCollection, $order);
        //calculate commission and shipping
        $distributedWalletAmountArr = $this->prepareSellerWiseWalletData(
            $sellerDistributedAmountArr,
            $walletAmount,
            $order
        );

        //start distribution of amount
        $this->startDistributionData($distributedWalletAmountArr, $order);
    }
    
    /**
     * Update Invoice function
     *
     * @param object $invoice
     * @return boolean
     */
    public function updateInvoice($invoice)
    {
        if (!$this->coreSession->getCustomInvoiceId()) {
            $this->coreSession->setCustomInvoiceId($invoice->getId());
        } else {
            $this->coreSession->unsetCustomInvoiceId();
            return false;
        }
        $mpOrderCollection = $this->mpOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('invoice_id', $invoice->getId());
        if (!$mpOrderCollection->getSize()) {
            $order = $this->orderModel->create()->load($invoice->getOrderId());
            if ($order->getWalletAmount()) {
                $walletAmount = -$order->getWalletAmount();
                $invoiceTotal = $invoice->getGrandTotal();
                $invoice->setGrandTotal($invoiceTotal + $walletAmount);
                $invoice->save();
                return true;
            }
        } else {
            $invoiceTotal = $invoice->getGrandTotal();
            foreach ($mpOrderCollection as $mpOrder) {
                $walletAmount = $mpOrder->getWalletAmount();
                $invoice->setGrandTotal($invoiceTotal + $walletAmount);
                $this->commitMethod($invoice);
                return true;
            }
        }
    }

    /**
     * Commit Method
     *
     * @param object $args
     */
    public function commitMethod($args)
    {
        $args->save();
    }

    /**
     * Delete Method
     *
     * @param object $args
     */
    public function deleteMethod($args)
    {
        $args->delete();
    }
}
