<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_CustomerSubaccount
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\CustomerSubaccount\Rewrite\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Directory\Model\AllowedCountries;
use Psr\Log\LoggerInterface;

class Multishipping extends \Magento\Multishipping\Model\Checkout\Type\Multishipping
{
    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @var \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Multishipping\PlaceOrderFactory
     */
    private $placeOrderFactory;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Quote\Model\Quote\AddressFactory $addressFactory
     * @param \Magento\Quote\Model\Quote\Address\ToOrder $quoteAddressToOrder
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress
     * @param \Magento\Quote\Model\Quote\Payment\ToOrderPayment $quotePaymentToOrderPayment
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $quoteItemToOrderItem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification
     * @param \Magento\Multishipping\Helper\Data $helper
     * @param OrderSender $orderSender
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param array $data
     * @param \Magento\Quote\Api\Data\CartExtensionFactory|null $cartExtensionFactory
     * @param AllowedCountries|null $allowedCountryReader
     * @param Multishipping\PlaceOrderFactory|null $placeOrderFactory
     * @param LoggerInterface|null $logger
     * @param \Magento\Framework\Api\DataObjectHelper|null $dataObjectHelper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\Generic $session,
        \Magento\Quote\Model\Quote\AddressFactory $addressFactory,
        \Magento\Quote\Model\Quote\Address\ToOrder $quoteAddressToOrder,
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress,
        \Magento\Quote\Model\Quote\Payment\ToOrderPayment $quotePaymentToOrderPayment,
        \Magento\Quote\Model\Quote\Item\ToOrderItem $quoteItemToOrderItem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification,
        \Magento\Multishipping\Helper\Data $helper,
        OrderSender $orderSender,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Webkul\CustomerSubaccount\Helper\Data $subaccHelper,
        array $data = [],
        \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory = null,
        AllowedCountries $allowedCountryReader = null,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderFactory $placeOrderFactory = null,
        LoggerInterface $logger = null,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper = null
    ) {
        parent::__construct(
            $checkoutSession,
            $customerSession,
            $orderFactory,
            $addressRepository,
            $eventManager,
            $scopeConfig,
            $session,
            $addressFactory,
            $quoteAddressToOrder,
            $quoteAddressToOrderAddress,
            $quotePaymentToOrderPayment,
            $quoteItemToOrderItem,
            $storeManager,
            $paymentSpecification,
            $helper,
            $orderSender,
            $priceCurrency,
            $quoteRepository,
            $searchCriteriaBuilder,
            $filterBuilder,
            $totalsCollector,
            $data,
            $cartExtensionFactory,
            $allowedCountryReader,
            $placeOrderFactory,
            $logger,
            $dataObjectHelper
        );

        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_session = $session;
        $this->_addressFactory = $addressFactory;
        $this->_storeManager = $storeManager;
        $this->paymentSpecification = $paymentSpecification;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->addressRepository = $addressRepository;
        $this->orderSender = $orderSender;
        $this->priceCurrency = $priceCurrency;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->helper = $helper;
        $this->quoteAddressToOrder = $quoteAddressToOrder;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->totalsCollector = $totalsCollector;
        $this->subaccHelper = $subaccHelper;
        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Quote\Api\Data\CartExtensionFactory::class);
        $this->allowedCountryReader = $allowedCountryReader ?: ObjectManager::getInstance()
            ->get(AllowedCountries::class);
        $this->placeOrderFactory = $placeOrderFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderFactory::class);
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
        $this->dataObjectHelper = $dataObjectHelper ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\DataObjectHelper::class);
        $this->_init();
    }

    /**
     * Assign quote items to addresses and specify items qty
     *
     * Array structure:
     * array(
     *      $quoteItemId => array(
     *          'qty'       => $qty,
     *          'address'   => $customerAddressId
     *      )
     * )
     *
     * @param array $infoa
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setShippingItemsInformation($infoa)
    {
        if (is_array($infoa)) {
            $allQty = 0;
            $itemsInfo = [];
            foreach ($infoa as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $allQty += $data['qty'];
                    $itemsInfo[$quoteItemId] = $data;
                }
            }

            $maxQty = $this->helper->getMaximumQty();
            if ($allQty > $maxQty) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        "The maximum quantity can't be more than %1 when shipping to multiple addresses. "
                        . "Change the quantity and try again.",
                        $maxQty
                    )
                );
            }
            $quote = $this->getQuote();
            $addresses = $quote->getAllShippingAddresses();
            foreach ($addresses as $address) {
                $quote->removeAddress($address->getId());
            }

            foreach ($infoa as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $this->_addShippingItem($quoteItemId, $data);
                }
            }

            $this->prepareShippingAssignment($quote);

            /**
             * Delete all not virtual quote items which are not added to shipping address
             * MultishippingQty should be defined for each quote item when it processed with _addShippingItem
             */
            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual() && !$_item->getParentItem() && !$_item->getMultishippingQty()
                ) {
                    $quote->removeItem($_item->getId());
                }
            }
            $b=1;
            $billingAddress = $quote->getBillingAddress();
            if ($billingAddress) {
                $quote->removeAddress($billingAddress->getId());
            }
            $a=1;
            $customerDefaultBillingId = $this->getCustomerDefaultBillingAddress();
            if ($customerDefaultBillingId) {
                $quote->getBillingAddress()->importCustomerAddressData(
                    $this->addressRepository->getById($customerDefaultBillingId)
                );
            }
            $a=1;

            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual()) {
                    continue;
                }
                $a=1;

                if (isset($itemsInfo[$_item->getId()]['qty'])) {
                    $qty = (int)$itemsInfo[$_item->getId()]['qty'];
                    if ($qty) {
                        $_item->setQty($qty);
                        $quote->getBillingAddress()->addItem($_item);
                    } else {
                        $_item->setQty(0);
                        $quote->removeItem($_item->getId());
                    }
                }
            }
            $temp=1;
            $this->save();
            $this->_eventManager->dispatch('checkout_type_multishipping_set_shipping_items', ['quote' => $quote]);
        }
        return $this;
    }

    /**
     * Check if specified address ID belongs to customer.
     *
     * @param mixed $addressId
     * @return bool
     */
    protected function isAddressIdApplicable($addressIda)
    {
        if ($this->subaccHelper->isForcedMainAddress()) {
            return true;
        }

        $applicableAddressIds = array_map(
            function ($address) {
                /** @var \Magento\Customer\Api\Data\AddressInterface $address */
                return $address->getId();
            },
            $this->getCustomer()->getAddresses()
        );

        return !is_numeric($addressIda) || in_array($addressIda, $applicableAddressIds);
    }

    /**
     * Add quote item to specific shipping address based on customer address id
     *
     * @param int $quoteItemId
     * @param array $data array('qty'=>$qty, 'address'=>$customerAddressId)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected function _addShippingItem($quoteItemIda, $data)
    {
        $qty = isset($data['qty']) ? (int)$data['qty'] : 1;
        //$qty       = $qty > 0 ? $qty : 1;
        $addressId = isset($data['address']) ? $data['address'] : false;
        $quoteItem = $this->getQuote()->getItemById($quoteItemIda);

        if ($addressId && $quoteItem) {
            if (!$this->isAddressIdApplicable($addressId)) {
                throw new LocalizedException(__('Verify the shipping address information and continue.'));
            }

            /**
             * Skip item processing if qty 0
             */
            if ($qty === 0) {
                return $this;
            }
            $a=1;
            $quoteItem->setMultishippingQty((int)$quoteItem->getMultishippingQty() + $qty);
            $quoteItem->setQty($quoteItem->getMultishippingQty());
            try {
                $address = $this->addressRepository->getById($addressId);
            } catch (\Exception $e) {
            }
            $a=1;
            if (isset($address)) {
                if (!($quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId()))) {
                    $quoteAddress = $this->_addressFactory->create()->importCustomerAddressData($address);
                    $this->getQuote()->addShippingAddress($quoteAddress);
                }

                $quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId());
                $quoteAddress->setCustomerAddressId($addressId);
                $quoteAddressItem = $quoteAddress->getItemByQuoteItemId($quoteItemIda);
                if ($quoteAddressItem) {
                    $quoteAddressItem->setQty((int)($quoteAddressItem->getQty() + $qty));
                } else {
                    $quoteAddress->addItem($quoteItem, $qty);
                }
                $a=1;
                /**
                 * Require shipping rate recollect
                 */
                $quoteAddress->setCollectShippingRates((bool)$this->getCollectRatesFlag());
            }
        }
        return $this;
    }

    /**
     * Reimport customer address info to quote shipping address
     *
     * @param int $addressId customer address id
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function updateQuoteCustomerShippingAddress($addressIda)
    {
        if (!$this->isAddressIdApplicable($addressIda)) {
            throw new LocalizedException(__('Verify the shipping address information and continue.'));
        }
        try {
            $address = $this->addressRepository->getById($addressIda);
        } catch (\Exception $e) {
            //
        }
        if (isset($address)) {
            $quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($addressIda);
            $quoteAddress->setCollectShippingRates(true)->importCustomerAddressData($address);
            $this->totalsCollector->collectAddressTotals($this->getQuote(), $quoteAddress);
            $this->quoteRepository->save($this->getQuote());
        }

        return $this;
    }

    /**
     * Reimport customer billing address to quote
     *
     * @param int $addressId customer address id
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function setQuoteCustomerBillingAddress($addressIda)
    {
        if (!$this->isAddressIdApplicable($addressIda)) {
            throw new LocalizedException(__('Verify the billing address information and continue.'));
        }
        try {
            $address = $this->addressRepository->getById($addressIda);
        } catch (\Exception $e) {
            //
        }
        if (isset($address)) {
            $quoteAddress = $this->getQuote()->getBillingAddress($addressIda)->importCustomerAddressData($address);
            $this->totalsCollector->collectAddressTotals($this->getQuote(), $quoteAddress);
            $this->getQuote()->collectTotals();
            $this->quoteRepository->save($this->getQuote());
        }

        return $this;
    }

    /**
     * Logs exceptions.
     *
     * @param \Exception[] $exceptionList
     * @return void
     */
    private function logExceptions(array $exceptionList)
    {
        foreach ($exceptionList as $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Prepare shipping assignment.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote
     */
    private function prepareShippingAssignment($quotea)
    {
        $cartExtension = $quotea->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $this->getShippingAssignmentProcessor()->create($quotea);
        $shipping = $shippingAssignment->getShipping();

        $shipping->setMethod(null);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quotea->setExtensionAttributes($cartExtension);
    }

    /**
     * Get shipping assignment processor.
     *
     * @return \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor
     */
    private function getShippingAssignmentProcessor()
    {
        if (!$this->shippingAssignmentProcessor) {
            $this->shippingAssignmentProcessor = ObjectManager::getInstance()
                ->get(\Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor::class);
        }
        return $this->shippingAssignmentProcessor;
    }

    /**
     * Validate minimum amount for "Checkout with Multiple Addresses" when
     * "Validate Each Address Separately in Multi-address Checkout" is No.
     *
     * @return bool
     */
    private function validateMinimumAmountForAddressItems()
    {
        $resulta = true;
        $storeId = $this->getQuote()->getStoreId();

        $minAmount = $this->_scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxInclude = $this->_scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $this->getQuote()->collectTotals();
        $addresses = $this->getQuote()->getAllAddresses();

        $baseTotal = 0;
        foreach ($addresses as $address) {
            $taxes = $taxInclude ? $address->getBaseTaxAmount() : 0;
            $baseTotal += $address->getBaseSubtotalWithDiscount() + $taxes;
        }

        if ($baseTotal < $minAmount) {
            $resulta = false;
        }

        return $resulta;
    }

    /**
     * Remove successfully placed items from quote.
     *
     * @param \Magento\Quote\Model\Quote\Address[] $shippingAddresses
     * @param int[] $placedAddressItems
     * @return void
     */
    private function removePlacedItemsFromQuote(array $shippingAddressess, array $placedAddressItems)
    {
        foreach ($shippingAddressess as $address) {
            foreach ($address->getAllItems() as $addressItem) {
                if (in_array($addressItem->getQuoteItemId(), $placedAddressItems)) {
                    if ($addressItem->getProduct()->getIsVirtual()) {
                        $addressItem->isDeleted(true);
                    } else {
                        $address->isDeleted(true);
                    }

                    $this->decreaseQuoteItemQty($addressItem->getQuoteItemId(), $addressItem->getQty());
                }
            }
        }
        $this->save();
    }

    /**
     * Decrease quote item quantity.
     *
     * @param int $quoteItemId
     * @param int $qty
     * @return void
     */
    private function decreaseQuoteItemQty(int $quoteItemId, int $qtya)
    {
        $quoteItem = $this->getQuote()->getItemById($quoteItemId);
        if ($quoteItem) {
            $newItemQty = $quoteItem->getQty() - $qtya;
            if ($newItemQty > 0) {
                $quoteItem->setQty($newItemQty);
            } else {
                $this->getQuote()->removeItem($quoteItem->getId());
                $this->getQuote()->setIsMultiShipping(1);
            }
        }
    }

    /**
     * Returns quote address id that was assigned to order.
     *
     * @param OrderInterface $order
     * @param \Magento\Quote\Model\Quote\Address[] $addresses
     *
     * @return int
     * @throws NotFoundException
     */
    private function searchQuoteAddressId(OrderInterface $order, array $addressesa): int
    {
        $items = $order->getItems();
        $item = array_pop($items);
        foreach ($addressesa as $address) {
            foreach ($address->getAllItems() as $addressItem) {
                if ($addressItem->getQuoteItemId() == $item->getQuoteItemId()) {
                    return (int)$address->getId();
                }
            }
        }

        throw new NotFoundException(__('Quote address for failed order not found.'));
    }

    /**
     * Get quote address errors.
     *
     * @param OrderInterface[] $orders
     * @param \Magento\Quote\Model\Quote\Address[] $addresses
     * @param \Exception[] $exceptionList
     * @return string[]
     * @throws NotFoundException
     */
    private function getQuoteAddressErrors(array $orders, array $addresses, array $exceptionList): array
    {
        $addressErrors = [];
        foreach ($orders as $failedOrder) {
            if (!isset($exceptionList[$failedOrder->getIncrementId()])) {
                throw new NotFoundException(__('Exception for failed order not found.'));
            }
            $addressId = $this->searchQuoteAddressId($failedOrder, $addresses);
            $addressErrors[$addressId] = $exceptionList[$failedOrder->getIncrementId()]->getMessage();
        }

        return $addressErrors;
    }

    /**
     * Returns quote address item id.
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getQuoteAddressItems(OrderInterface $ordera): array
    {
        $placedAddressItems = [];
        foreach ($ordera->getItems() as $orderItem) {
            $placedAddressItems[] = $orderItem->getQuoteItemId();
        }

        return $placedAddressItems;
    }

    /**
     * Returns placed address items
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getPlacedAddressItems(OrderInterface $order): array
    {
        $placedAddressItems = [];
        foreach ($this->getQuoteAddressItems($order) as $key => $quoteAddressItem) {
            $placedAddressItems[$key] = $quoteAddressItem;
        }

        return $placedAddressItems;
    }
}
