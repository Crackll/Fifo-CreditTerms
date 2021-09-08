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
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Session\SessionManager;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Webkul MpWalletSystem Observer Class
 */
class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $dataHelper;
    
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
     * @var QuoteRepository
     */
    protected $quoteRepository;
    
    /**
     * @var SessionManager
     */
    protected $coreSession;
    
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * Initialize dependencies
     *
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Webkul\MpWalletSystem\Helper\Data          $dataHelper
     * @param \Webkul\MpWalletSystem\Logger\Logger        $log
     * @param \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayments
     * @param QuoteRepository                             $quoteRepository
     * @param SessionManager                              $coreSession
     * @param OrderRepositoryInterface                    $orderRepository
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\MpWalletSystem\Helper\Data $dataHelper,
        \Webkul\MpWalletSystem\Logger\Logger $log,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayments,
        QuoteRepository $quoteRepository,
        SessionManager $coreSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dataHelper = $dataHelper;
        $this->logger = $log;
        $this->splitPayments = $splitPayments;
        $this->quoteRepository = $quoteRepository;
        $this->coreSession = $coreSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Sales order place after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $isMultiShipping = $this->checkoutSession->getQuote()->getIsMultiShipping();
            if (!$isMultiShipping) {
                $walletProductId = $this->dataHelper->getWalletProductId();
                $orderId = $observer->getOrder()->getId();
                $order = $this->orderRepository->get($orderId);
                if ($this->splitPayments->alreadyAddedInData($order)) {
                    return;
                }
                $this->splitPayments->setDataInWalletTable($orderId, $order);
            } else {
                $quoteId = $this->checkoutSession->getLastQuoteId();
                $quote = $this->quoteRepository->get($quoteId);
                if ($quote->getIsMultiShipping() == 1 || $isMultiShipping == 1) {
                    $orderIds = $this->coreSession->getOrderIds();
                    foreach ($orderIds as $orderId => $orderIncId) {
                        $lastOrderId = $orderId;
                        $order = $this->orderRepository->get($lastOrderId);
                        if ($this->splitPayments->alreadyAddedInData($order)) {
                            continue;
                        }
                        $this->splitPayments->setDataInWalletTable($lastOrderId, $order);
                    }
                }
            }
            $this->checkoutSession->unsWalletDiscount();
        } catch (\Exception $e) {
            $this->logger->addInfo('Error creating Invoice '.$e->getMessage());
        }
    }
}
