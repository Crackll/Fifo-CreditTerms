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

use Webkul\MpWalletSystem\Model\WalletcreditrulesFactory;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Webkul MpWalletSystem Helper Class
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const WALLET_PRODUCT_SKU = 'wk_wallet_amount';
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var Session
     */
    protected $customerSession;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;
    
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletrecordFactory
     */
    protected $walletRecordFactory;
    
    /**
     * @var Magento\Directory\Helper\Data
     */
    protected $directoryHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletcreditrulesFactory
     */
    protected $walletcreditrulesFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var itemFactory
     */
    protected $itemFactory;
    
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $orderModel;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WallettransactionFactory
     */
    protected $walletTransaction;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Mail
     */
    protected $mailHelper;
    
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    
    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;
    
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cartModel;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;
    
    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;
    
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * Initialize dependencies
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Checkout\Model\Session                      $checkoutSession
     * @param \Magento\Framework\Locale\CurrencyInterface          $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Directory\Model\Currency                    $currency
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface    $priceCurrency
     * @param \Magento\Catalog\Model\ProductFactory                $productFactory
     * @param \Magento\Framework\Pricing\Helper\Data               $pricingHelper
     * @param \Webkul\MpWalletSystem\Model\WalletrecordFactory     $walletRecord
     * @param \Magento\Directory\Helper\Data                       $directoryHelper
     * @param WalletcreditrulesFactory                             $walletcreditrulesFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime          $date
     * @param \Magento\Framework\Serialize\SerializerInterface     $serialize
     * @param ItemFactory                                          $itemFactory
     * @param OrderFactory                                         $orderModel
     * @param WallettransactionFactory                             $walletTransaction
     * @param \Magento\Framework\Translate\Inline\StateInterface   $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder    $transportBuilder
     * @param \Magento\Customer\Model\CustomerFactory              $customerFactory
     * @param QuoteFactory                                         $quoteFactory
     * @param \Webkul\MpWalletSystem\Logger\Logger                 $log
     * @param PaymentTransaction\BuilderInterface                  $transactionBuilder
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     * @param \Magento\Checkout\Model\Cart                         $cartModel
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository
     * @param \Magento\Framework\App\Request\Http                  $request
     * @param ProductMetadataInterface                             $productMetaData
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Webkul\MpWalletSystem\Model\WalletrecordFactory $walletRecord,
        \Magento\Directory\Helper\Data $directoryHelper,
        WalletcreditrulesFactory $walletcreditrulesFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Serialize\SerializerInterface $serialize,
        ItemFactory $itemFactory,
        OrderFactory $orderModel,
        WallettransactionFactory $walletTransaction,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QuoteFactory $quoteFactory,
        \Webkul\MpWalletSystem\Logger\Logger $log,
        PaymentTransaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Request\Http $request,
        ProductMetadataInterface $productMetaData
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $log;
        $this->localeCurrency = $localeCurrency;
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->productFactory = $productFactory;
        $this->pricingHelper = $pricingHelper;
        $this->walletRecordFactory = $walletRecord;
        $this->serialize = $serialize;
        $this->request = $request;
        $this->directoryHelper = $directoryHelper;
        $this->walletcreditrulesFactory = $walletcreditrulesFactory;
        $this->date = $date;
        $this->itemFactory = $itemFactory;
        $this->orderModel = $orderModel;
        $this->walletTransaction = $walletTransaction;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->customerModel = $customerFactory;
        $this->quoteFactory = $quoteFactory;
        $this->messageManager = $messageManager;
        $this->cartModel = $cartModel;
        $this->localeDate = $localeDate;
        $this->stockRegistry = $stockRegistry;
        $this->transactionBuilder = $transactionBuilder;
        $this->productRepository = $productRepository;
        $this->productMetaData = $productMetaData;
    }

    /**
     * Return customer id from customer session
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }

    /**
     * Return if Webkul_Mpsplitorder module is enabled or not
     *
     * @return bool
     */
    public function isEnabledMpsplitorder()
    {
        return $this->scopeConfig->getValue(
            'marketplace/mpsplitorder/mpsplitorder_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return wallet amount is enabled or not
     *
     * @return bool
     */
    public function getWalletenabled()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpwalletsystem/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Return configuration name of wallet system payment method
     *
     * @return string
     */
    public function getWalletTitle()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpwalletsystem/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet product id
     *
     * @return int
     */
    public function getWalletProductId()
    {
        $walletProductId = $this->productFactory->create()
            ->getIdBySku(self::WALLET_PRODUCT_SKU);

        return $walletProductId;
    }

    /**
     * Maximum amount set in system config
     *
     * @return int
     */
    public function getMaximumAmount()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpwalletsystem/maximumamounttoadd',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return minimum amount set in system config
     *
     * @return string
     */
    public function getMinimumAmount()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpwalletsystem/minimumamounttoadd',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return payment methods selected in system config
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpwalletsystem/allowpaymentmethod',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get price priority set in system config
     *
     * @return string
     */
    public function getPricePriority()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/general_settings/priority',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get price type set insystem config
     *
     * @return string
     */
    public function getPriceType()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/general_settings/price_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get sysem config transfer
     *
     * @return void
     */
    public function getTransferValidationEnable()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/transfer_settings/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Code validation duration
     *
     * @return int
     */
    public function getCodeValidationDuration()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/transfer_settings/duration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin credit prefix
     *
     * @return string
     */
    public function getAdminCreditPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/prefix_admin_credit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin debit prefix
     *
     * @return string
     */
    public function getAdminDebitPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/prefix_admin_debit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer credit prefix
     *
     * @return string
     */
    public function getCustomerCreditPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/prefix_customer_credit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer debit prefix
     *
     * @return string
     */
    public function getCustomerDebitPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/prefix_customer_debit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get order credit prefix
     *
     * @return string
     */
    public function getOrderCreditPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/prefix_order_credit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get order debit prefix
     *
     * @return string
     */
    public function getOrderDebitPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/prefix_order_debit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cahsback prefix
     *
     * @return string
     */
    public function getcashbackprefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/cashback_prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get refund order prefix
     *
     * @return string
     */
    public function getrefundOrderPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/refund_order_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet order refund prefix
     *
     * @return string
     */
    public function getWalletOrderRefundPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/refund_wallet_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet bank transfer prefix
     *
     * @return bool
     */
    public function getWalletBankTransferPrefix()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/prefixfortransaction/bank_transfer_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cron enable
     *
     * @return bool
     */
    public function getcronEnable()
    {
        return  $this->scopeConfig->getValue(
            'mpwalletsystem/cron_jobs/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get discount enable
     *
     * @return bool
     */
    public function getDiscountEnable()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/addingwallet_settings/discount_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Payee Status
     *
     * @return int
     */
    public function getPayeeStatus()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/transfer_settings/payeestatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return currency currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Get all allowed currency in system config
     *
     * @return array
     */
    public function getConfigAllowCurrencies()
    {
        return $this->currency->getConfigAllowCurrencies();
    }

    /**
     * Get currency rates
     *
     * @param string $currency
     * @param string $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    /**
     * Get currency symbol of an currency code
     *
     * @param string $currencycode
     * @return void
     */
    public function getCurrencySymbol($currencycode)
    {
        $currency = $this->localeCurrency->getCurrency($currencycode);

        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

    /**
     * Get formatted price according to currenct currency
     *
     * @param int $price
     * @return int
     */
    public function getformattedPrice($price)
    {
        return $this->pricingHelper
            ->currency($price, true, false);
    }

    /**
     * Get remaining total wallet amount of a customer
     *
     * @param int $customerId
     * @return int $amount
     */
    public function getWalletTotalAmount($customerId)
    {
        if (!$customerId) {
            $customerId = $this->getCustomerId();
        }
        $amount = 0;
        $walletRecord = $this->walletRecordFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($walletRecord->getSize()) {
            foreach ($walletRecord as $record) {
                $amount = $record->getRemainingAmount();
            }
        }

        return $amount;
    }

    /**
     * Get total wallet amount
     *
     * @param int $customerId
     * @return int $amount
     */
    public function getTotalWalletAmount($customerId)
    {
        if (!$customerId) {
            $customerId = $this->getCustomerId();
        }
        $amount = 0;
        $walletRecord = $this->walletRecordFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($walletRecord->getSize()) {
            foreach ($walletRecord as $record) {
                $amount = $record->getTotalAmount();
            }
        }

        return $amount;
    }

    /**
     * Check wallet amount is currently in use or not
     *
     * @return bool
     */
    public function getWalletStatus()
    {
        $status = false;
        if ($this->checkoutSession->getWalletDiscount()) {
            $walletSession = $this->checkoutSession->getWalletDiscount();
            if (array_key_exists('type', $walletSession) && $walletSession['type'] == 'set') {
                $status = true;
            } else {
                $status = false;
            }
        }

        return $status;
    }

    /**
     * Calculate grand total
     *
     * @return int
     */
    public function getGrandTotal()
    {
        $grandTotal = 0;
        $totals = $this->checkoutSession->getQuote()->getTotals();
        if (array_key_exists('grand_total', $totals)) {
            $grandTotal = $totals['grand_total']->getValue();
        } else {
            $grandTotal = $this->checkoutSession->getQuote()->getGrandTotal();
        }

        return $grandTotal;
    }

    /**
     * Get sub total
     *
     * @return int $subTotal
     */
    public function getSubTotal()
    {
        $subTotal = 0;
        $totals = $this->checkoutSession->getQuote()->getTotals();
        if (array_key_exists('subtotal', $totals)) {
            $subTotal = $totals['subtotal']->getValue();
        } else {
            $subTotal = $this->checkoutSession->getQuote()->getSubtotal();
        }

        return $subTotal;
    }

    /**
     * Check how much amount is left to pay
     *
     * @return int
     */
    public function getlefToPayAmount()
    {
        $grandTotal = 0;
        $grandTotal = $this->getGrandTotal();
        $leftamount = $grandTotal;
        if ($this->checkoutSession->getWalletDiscount()) {
            $walletSession = $this->checkoutSession->getWalletDiscount();
            if (array_key_exists('grand_total', $walletSession) && $walletSession['grand_total'] != $grandTotal) {
                $walletSession['grand_total'] = $grandTotal;
                $walletSession['amount'] = 0;
                $walletSession['type'] = 'reset';
                $this->checkoutSession->setWalletDiscount($walletSession);
            }
            if (array_key_exists('amount', $walletSession)) {
                $leftamount = $grandTotal - $walletSession['amount'];
            }
        }
        return $leftamount;
    }

    /**
     * Calculate how much amount is left in cart or not
     *
     * @return int
     */
    public function getLeftInWallet()
    {
        $leftinWallet = $this->getWalletTotalAmount($this->getCustomerId());
        if ($this->checkoutSession->getWalletDiscount()) {
            $walletSession = $this->checkoutSession->getWalletDiscount();
            if (is_array($walletSession) &&
                array_key_exists('grand_total', $walletSession) &&
                array_key_exists('type', $walletSession) &&
                $walletSession['type'] == 'set' && $walletSession['grand_total']) {
                $leftinWallet = $leftinWallet - $walletSession['grand_total'];
            }
        }

        return $this->getformattedPrice($leftinWallet);
    }

    /**
     * Check payment method is enable or not and is wallet product is in cart or not
     *
     * @return bool
     */
    public function getPaymentisEnabled()
    {
        if ($this->getWalletenabled() && $this->getCustomerId()) {
            $walletProductId = $this->getWalletProductId();
            $cartData = $this->checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($cartData as $item) {
                if ($item->getProduct()->getId() == $walletProductId) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get admin default email id
     *
     * @return mixed
     */
    public function getDefaultTransEmailId()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check whether wallet product is in cart or not
     *
     * @return bool
     */
    public function getCartStatus()
    {
        if ($this->customerSession->isLoggedIn()) {
            $cart = $this->checkoutSession
                ->getQuote()
                ->getAllItems();
            $productIds = [];
            $walletProductId = $this->getWalletProductId();
            if (!empty($cart)) {
                foreach ($cart as $item) {
                    $productIds[] = $item->getProduct()->getId();
                }
                if (!empty($productIds)) {
                    if (!in_array($walletProductId, $productIds)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Convert currency amount
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param int $amount
     * @return int $currencyAmount
     */
    public function getwkconvertCurrency($fromCurrency, $toCurrency, $amount)
    {
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$fromCurrency])) {
            $rates[$fromCurrency] = 1;
        }

        if ($baseCurrencyCode==$toCurrency) {
            $currencyAmount = $amount/$rates[$fromCurrency];
        } else {
            $amount = $amount/$rates[$fromCurrency];
            $currencyAmount = $this->convertCurrency($amount, $baseCurrencyCode, $toCurrency);
        }
        return $currencyAmount;
    }

    /**
     * Get amount in base currency amount from current currency
     *
     * @param int $amount
     * @param int $store
     * @return void
     */
    public function baseCurrencyAmount($amount, $store = null)
    {
        if ($store == null) {
            $store = $this->storeManager->getStore()->getStoreId();
        }
        if ($amount == 0) {
            return $amount;
        }
        $rate = $this->priceCurrency->convert($amount, $store) / $amount;
        $amount = $amount / $rate;

        return round($amount, 4);
    }

    /**
     * Convert amount according to currenct currency
     *
     * @param int $amount
     * @param string $from
     * @param string $to
     * @return int $finalAmount
     */
    public function convertCurrency($amount, $from, $to)
    {
        $finalAmount = $this->directoryHelper
            ->currencyConvert($amount, $from, $to);

        return $finalAmount;
    }

    /**
     * Get current store
     *
     * @return object
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Get currenct currency amount from base
     *
     * @param int $amount
     * @param int $store
     * @return int
     */
    public function currentCurrencyAmount($amount, $store = null)
    {
        if ($store == null) {
            $store = $this->storeManager->getStore()->getStoreId();
        }
        $returnAmount = $this->priceCurrency->convert($amount, $store);

        return round($returnAmount, 4);
    }

    /**
     * Url of controller in which apply wallet amount on order
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('mpwalletsystem/index/applypaymentamount');
    }

    /**
     * Calculate credit amount for cart
     * Priority 0 product based,1 cart based
     * @param int $orderId
     * @return int
     */
    public function calculateCreditAmountforCart($orderId = 0)
    {
        $creditRuleModel = $this->walletcreditrulesFactory->create();
        $walletProductId = $this->getWalletProductId();
        $creditAmount = 0;
        $priority = $this->getPricePriority();
        if ($orderId!=0) {
            $order = $this->orderModel->create()->load($orderId);
        }
        if ($priority==$creditRuleModel::WALLET_CREDIT_CONFIG_PRIORITY_PRODUCT_BASED) {
            //product based
            if ($orderId!=0) {
                $cartData = $order->getAllItems();
            } else {
                $cartData = $this->cartModel->getQuote()->getAllItems();
            }
            foreach ($cartData as $item) {
                $price = 0;
                $qty = 0;
                if ($item->getProduct()->getId() == $walletProductId || $item->getParentItem()) {
                    continue;
                } else {
                    $productId = $item->getProduct()->getId();
                    $creditAmount += $this->getProductData($item);
                }
            }
        } else {
            //cart based
            $basedOn = $creditRuleModel::WALLET_CREDIT_RULE_BASED_ON_CART;
            if ($orderId) {
                $amount = $order->getSubtotal();
            } else {
                $amount = $this->getSubTotal();
            }
            $returnAmount = $this->getPriceBasedOnRules($basedOn, $amount);
            $priceType = $this->getPriceType();
            $creditAmount = $this->getPriceByType($priceType, $returnAmount, $amount);
        }
        if (!$creditAmount) {
            return 0;
        }
        return $creditAmount;
    }

    /**
     * Initialize dependencies
     *
     * @param int $productId
     * @return void
     */
    public function getProduct($productId)
    {
        return $this->productFactory->create()->load($productId);
    }

    /**
     * Get price based on rules
     *
     * @param string $type
     * @param int $minimumAmount
     * @return int $amount
     */
    public function getPriceBasedOnRules($type, $minimumAmount)
    {
        $walletcreditModel = $this->walletcreditrulesFactory->create();
        $today = $this->date->gmtDate('Y-m-d');
        $amount = 0;
        $creditruleCollection = $this->walletcreditrulesFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('status', $walletcreditModel::WALLET_CREDIT_RULE_STATUS_ENABLE)
            ->addFieldToFilter('based_on', $type)
            ->addFieldToFilter('start_date', ['lteq' => $today])
            ->addFieldToFilter('end_date', ['gteq' => $today])
            ->addFieldToFilter('minimum_amount', ['lteq'=>$minimumAmount])
            ->setOrder('amount', 'desc');

        if ($creditruleCollection->getSize()) {
            foreach ($creditruleCollection as $creditRule) {
                $amount = $creditRule->getAmount();
                break;
            }
        }
        return $amount;
    }

    /**
     * Get product data
     *
     * @param item $item
     * @return int $returnAmount
     */
    public function getProductData($item)
    {
        $productId = $item->getProduct()->getId();
        $creditPrice = 0;
        $qty = 0;
        $creditPrice = $this->getProductCreditPrice($item->getProductId(), $item->getPrice());
        if ($item->getOrderId() && $item->getOrderId()!=0) {
            $qty = $item->getQtyOrdered();
        } else {
            $qty = $item->getQty();
        }
        $returnAmount = $creditPrice * $qty;
        return $returnAmount;
    }

    /**
     * Get product credit price
     *
     * @param int $productId
     * @param int $amount
     * @return int $returnPrice
     */
    public function getProductCreditPrice($productId, $amount)
    {
        $creditRuleModel = $this->walletcreditrulesFactory->create();
        $product = $this->getProduct($productId);
        $price = 0;
        $productCreditAmountBasedOn = $product->getWalletCreditBasedOn();
        if ($productCreditAmountBasedOn == $creditRuleModel::WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_PRODUCT) {
            //product cash back amount
            $price = $product->getWalletCashBack();
        } elseif ($productCreditAmountBasedOn == $creditRuleModel::WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_RULE) {
            //rule based
            $basedOn = $creditRuleModel::WALLET_CREDIT_RULE_BASED_ON_PRODUCT;
            $price = $this->getPriceBasedOnRules($basedOn, $amount);
        }
        $priceType = $this->getPriceType();
        $returnPrice = $this->getPriceByType($priceType, $price, $amount);
        return $returnPrice;
    }

    /**
     * Get price by type
     *
     * @param string $type
     * @param int $price
     * @param int $amount
     * @return int
     */
    public function getPriceByType($type, $price, $amount)
    {
        $creditRuleModel = $this->walletcreditrulesFactory->create();
        if ($type==$creditRuleModel::WALLET_CREDIT_CONFIG_AMOUNT_TYPE_FIXED) {
            //fixed
            return $price;
        } else {
            //percent
            if ($price > 100) {
                $price = 100;
            }
            $percentAmount = ($amount*$price)/100;
            return $percentAmount;
        }
    }

    /**
     * Check wallet product with other product
     *
     * @return void
     */
    public function checkWalletproductWithOtherProduct()
    {
        $walletInCart = 0;
        $otherInCart = 0;
        $itemIds = '';
        $quote = '';
        if ($this->checkoutSession->getQuoteId()) {
            $quoteId = $this->checkoutSession->getQuoteId();
            $quote = $this->quoteFactory->create()
                ->load($quoteId);
        }
        if ($quote) {
            $cartData = $quote->getAllVisibleItems();
            if (!empty($cartData)) {
                $walletProductId = $this->getWalletProductId();
                foreach ($cartData as $cart) {
                    if ($cart->getProduct()->getId() == $walletProductId) {
                        $itemIds = $cart->getItemId();
                        $price = $cart->getCustomPrice();
                        $walletInCart = 1;
                    } else {
                        $otherInCart = 1;
                    }
                }
                if ($walletInCart==1 && $otherInCart==1 && $itemIds!='') {
                    $quote = $this->itemFactory->create()->load($itemIds);
                    $quote->delete();
                }
            }
        }
    }

    /**
     * Get customer data by customer id
     *
     * @param int $customerId
     * @return void
     */
    public function getCustomerByCustomerId($customerId)
    {
        return $this->customerModel->create()->load($customerId);
    }

    /**
     * Get formatted price according to currency
     *
     * @param int $amount
     * @param int $precision
     * @param string $currencyCode
     * @return string
     */
    public function getFormattedPriceAccToCurrency($amount, $precision, $currencyCode)
    {
        $precision = 2;
        return $this->priceCurrency->format(
            $amount,
            $includeContainer = false,
            $precision,
            $scope = null,
            $currencyCode
        );
    }

    /**
     * Initialize dependencies
     *
     * @param string $senderType
     * @param action $action
     * @return void
     */
    public function getTransactionPrefix($senderType, $action)
    {
        $walletTransaction = $this->walletTransaction->create();
        if ($senderType==$walletTransaction::ORDER_PLACE_TYPE) {
            return $this->getOrderPlaceType($action, $walletTransaction);
        } elseif ($senderType==$walletTransaction::CASH_BACK_TYPE) {
            return $this->getcashbackprefix();
        } elseif ($senderType==$walletTransaction::REFUND_TYPE) {
            return $this->getRefundType($action, $walletTransaction);
        } elseif ($senderType==$walletTransaction::ADMIN_TRANSFER_TYPE) {
            return $this->getadminTransferType($action, $walletTransaction);
        } elseif ($senderType==$walletTransaction::CUSTOMER_TRANSFER_TYPE) {
            if ($action==$walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getCustomerCreditPrefix();
            } else {
                return $this->getCustomerDebitPrefix();
            }
        } elseif ($senderType==$walletTransaction::CUSTOMER_TRANSFER_BANK_TYPE) {
            if ($action==$walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                return $this->getWalletBankTransferPrefix();
            }
        }
        return __("Wallet Transaction");
    }

    /**
     * Validate script tag
     *
     * @param string $string
     * @return string $string
     */
    public function validateScriptTag($string)
    {
        if ($string!='') {
            $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        }
        return $string;
    }

    /**
     * Get wallet recharge template id for customer
     *
     * @return mixed
     */
    public function getWalletRechargeTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/recharge_wallet_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet recharge template id for admin
     *
     * @return mixed
     */
    public function getWalletRechargeTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/recharge_wallet_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet used template for customer
     *
     * @return mixed
     */
    public function getWalletUsedTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/used_wallet_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet used template id for admin
     *
     * @return mixed
     */
    public function getWalletUsedTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/used_wallet_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet cashback template id for customer
     *
     * @return mixed
     */
    public function getWalletCashbackTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/cashback_wallet_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet cashback template id for admin
     *
     * @return mixed
     */
    public function getWalletCashbackTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/cashback_wallet_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet amount refund template id for customer
     *
     * @return mixed
     */
    public function getWalletAmountRefundTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/refund_wallet_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet amount refund template id for admin
     *
     * @return mixed
     */
    public function getWalletAmountRefundTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/refund_wallet_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet order refund template id for customer
     *
     * @return mixed
     */
    public function getWalletOrderRefundTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/refund_order_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get wallet order refund template id for admin
     *
     * @return mixed
     */
    public function getWalletOrderRefundTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/refund_order_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin credit admount template id for customer
     *
     * @return mixed
     */
    public function getAdminCreditAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/admin_credit_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin credit amount template id for admin
     *
     * @return mixed
     */
    public function getAdminCreditAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/admin_credit_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin debit amount template id for customer
     *
     * @return mixed
     */
    public function getAdminDebitAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/admin_debit_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin debit amount template id for admin
     *
     * @return mixed
     */
    public function getAdminDebitAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/admin_debit_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer debit amount template id for customer
     *
     * @return mixed
     */
    public function getCustomerDebitAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/customer_debit_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer debit amount template id for admin
     *
     * @return mixed
     */
    public function getCustomerDebitAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/customer_debit_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer credit amount template id for customer
     *
     * @return mixed
     */
    public function getCustomerCreditAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/customer_credit_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer credit amount template id for admin
     *
     * @return mixed
     */
    public function getCustomerCreditAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/customer_credit_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer amount transfer otp template id
     *
     * @return mixed
     */
    public function getCustomerAmountTransferOTPTemplateId()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/sendcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get monthly statement template id
     *
     * @return mixed
     */
    public function getMonthlystatementTemplateId()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/monthlystatement',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer bank transfer amount template it for admin
     *
     * @return mixed
     */
    public function getCustomerBankTansferAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/customer_bank_transfer_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer bank transfer amount template for customer
     *
     * @return mixed
     */
    public function getCustomerBankTansferAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/customer_bank_transfer_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Customer Bulk Transfer Approve Mail function
     *
     * @return mixed
     */
    public function getCustomerBulkTransferApproveMail()
    {
        return $this->scopeConfig->getValue(
            'mpwalletsystem/email_template/customer_bank_transfer_approve',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Loadds product by sku
     *
     * @param int $sku
     * @return object
     */
    public function loadProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * Converts string according to version
     *
     * @param string $string
     * @param string $type
     * @return string
     */
    public function convertStringAccToVersion($string, $type)
    {
        if ($string!='') {
            $magentoVersion = $this->productMetaData->getVersion();
            if (version_compare($magentoVersion, '2.2.0')) {
                if ($type=='encode') {
                    return json_encode($string);
                } else {
                    $object = json_decode($string);
                    if (is_object($object)) {
                        return json_decode(json_encode($object), true);
                    }
                    return $object;
                }
            } else {
                $serializeInterface = $this->serialize;
                if ($type=='encode') {
                    return $serializeInterface->serialize($string);
                } else {
                    return $serializeInterface->unserialize($string);
                }
            }
        }
        return $string;
    }

    /**
     * Get wallet amount details of logged in customer
     *
     * @return void
     */
    public function getWalletDetailsData()
    {
        $walletProductId = $this->getWalletProductId();
        $customerId = $this->getCustomerId();
        $customerName = $this->getCustomerByCustomerId($customerId)
            ->getName();
        $currencySymbol = $this->getCurrencySymbol(
            $this->getCurrentCurrencyCode()
        );
        $currentWalletamount = $this->getWalletTotalAmount($customerId);

        $base = $this->getBaseCurrencyCode();
        $current = $this->getCurrentCurrencyCode();
        $currentWalletamount = $this->getwkconvertCurrency($base, $current, $currentWalletamount);

        $returnDataArray = [
            'customer_name' => $customerName,
            'wallet_amount' => $currentWalletamount,
            'walletProductId' => $walletProductId,
            'currencySymbol' => $currencySymbol
        ];
        return $returnDataArray;
    }

    /**
     * Calidate amount for customer
     *
     * @param int $amount
     * @param int $customerId
     * @return bool
     */
    public function validateAmountForCustomer($amount, $customerId)
    {
        $base = $this->getBaseCurrencyCode();
        $current = $this->getCurrentCurrencyCode();
        $amount = $this->getwkconvertCurrency($current, $base, $amount);
        $currentWalletamount = $this->getWalletTotalAmount($customerId);
        if ($currentWalletamount >= $amount) {
            return true;
        }
        return false;
    }

    /**
     * Get order from url
     *
     * @return int $id
     */
    public function getOrderIdFromUrl()
    {
        $request = $this->request;
        if (!empty($request->getParam('order_id'))) {
            return $request->getParam('order_id');
        }
        return $request->getParam('id');
    }

    /**
     * Check Invoice Id In Url function
     *
     * @return int
     */
    public function checkInvoiceIdInUrl()
    {
        $request = $this->request;
        if (!empty($request->getParam('invoice_id'))) {
            return $invoiceId = $request->getParam('invoice_id');
        }
    }

    /**
     * Check wallet product in order items
     *
     * @return bool
     */
    public function checkWalletProductInOrderItems()
    {
        $orderId = $this->getOrderIdFromUrl();
        $flag = false;
        $order = $this->orderModel->create()->load($orderId);
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $item) {
            if ($item->getSku() == "wk_wallet_amount") {
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * Create Transaction function
     *
     * @param object $order
     * @param int $price
     * @return string
     */
    public function createTransaction($order, $price)
    {
        $transId = 'wk_wallet_system'.$order->getId().'-'.rand();
        $transArray = [
            'id' => $transId,
            'amount' => - (int) $order->getWalletAmount()
        ];

        $payment = $order->getPayment();
        $payment->setLastTransId($transArray['id']);
        $payment->setTransactionId($transArray['id']);
        $payment->setAdditionalInformation(
            [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $transArray]
        );
        $formatedPrice = $order->getBaseCurrency()->formatTxt(
            $price
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
    }

    /**
     * Get order place type template
     *
     * @param string $action
     * @param string $walletTransaction
     * @return string
     */
    public function getOrderPlaceType($action, $walletTransaction)
    {
        if ($action==$walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getOrderCreditPrefix();
        } else {
            return $this->getOrderDebitPrefix();
        }
    }

    /**
     * Get refund type
     *
     * @param string $action
     * @param string $walletTransaction
     * @return string
     */
    public function getRefundType($action, $walletTransaction)
    {
        if ($action==$walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getrefundOrderPrefix();
        } else {
            return $this->getWalletOrderRefundPrefix();
        }
    }

    /**
     * Get admin transfer type
     *
     * @param string $action
     * @param string $walletTransaction
     * @return string
     */
    public function getadminTransferType($action, $walletTransaction)
    {
        if ($action==$walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getAdminCreditPrefix();
        } else {
            return $this->getAdminDebitPrefix();
        }
    }

    /**
     * Format amount as per currency
     *
     * @param  int $amount
     * @return int
     */
    public function formatAmount($amount)
    {
        $baseCurrenyCode = $this->getBaseCurrencyCode();
        $currencySymbol = $this->getCurrencySymbol(
            $this->getCurrentCurrencyCode()
        );
        $currentCurrenyCode = $this->getCurrentCurrencyCode();
        return $finalminimumAmount = $this->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $amount
        );
    }
}
