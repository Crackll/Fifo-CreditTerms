<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Helper;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Magento\Sales\Model\OrderFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollection;
use Webkul\MpRewardSystem\Api\Data\RewardcategoryInterfaceFactory;
use Webkul\MpRewardSystem\Api\Data\RewarddetailInterfaceFactory;
use Webkul\MpRewardSystem\Api\Data\RewardproductInterfaceFactory;
use Webkul\MpRewardSystem\Api\Data\RewardrecordInterfaceFactory;
use Webkul\MpRewardSystem\Api\RewardcategoryRepositoryInterface;
use Webkul\MpRewardSystem\Api\RewarddetailRepositoryInterface;
use Webkul\MpRewardSystem\Api\RewardproductRepositoryInterface;
use Webkul\MpRewardSystem\Api\RewardrecordRepositoryInterface;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardcart\CollectionFactory as RewardcartCollection;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardcategory\CollectionFactory as RewardcategoryCollection;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardproduct\CollectionFactory as RewardProductCollection;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardrecord\CollectionFactory as RewardRecordCollection;
use Webkul\MpRewardSystem\Model\RewarddetailFactory as RewarddetailFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper implements ArgumentInterface
{

    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelperData;
    /**
     * @var SellerCollection
     */
    protected $sellerCollection;
    /**
     * @var CustomerCollection
     */
    protected $customerCollection;
    /**
     * @var \Webkul\MpRewardSystem\Api\Data\RewardproductInterfaceFactory;
     */
    protected $rewardProductInterface;
    /**
     * @var \Webkul\MpRewardSystem\Api\Data\RewardcategoryInterfaceFactory;
     */
    protected $rewardCategoryInterface;
    /**
     * @var \Webkul\MpRewardSystem\Api\RewardproductRepositoryInterface;
     */
    protected $rewardProductRepository;
    /**
     * @var \Webkul\MpRewardSystem\Api\RewardcategoryRepositoryInterface;
     */
    protected $rewardCategoryRepository;
    /**
     * @var RewarddetailInterfaceFactory;
     */
    protected $rewardDetailInterface;
    /**
     * @var RewarddetailRepositoryInterface
     */
    protected $rewardDetailRepository;
    /**
     * @var RewardRecordCollection;
     */
    protected $rewardRecordCollection;
    /**
     * @var RewardrecordRepositoryInterface;
     */
    protected $rewardRecordRepository;
    /**
     * @var RewardrecordInterfaceFactory;
     */
    protected $rewardRecordInterface;
    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;
    /**
     * @var \Webkul\MpRewardSystem\Helper\Mail
     */
    protected $mailHelper;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var \Webkul\MpRewardSystem\Model\Config\Source\Priority
     */
    protected $prioritySet;
    /**
     * @var OptionFactory
     */
    protected $itemOptionModel;
    /**
     * @var Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;
    /**
     * @var RewardcartCollection;
     */
    protected $rewardcartCollection;
    /**
     * @var RewardProductCollection;
     */
    protected $rewardProductCollection;
    /**
     * @var RewardcategoryCollection;
     */
    protected $rewardcategoryCollection;
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $orderModel;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cartModel;
    /**
     * @var
     */
    protected $itemsFactory;
    /**
     * @var
     */
    protected $dataFactory;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var \Webkul\MpRewardSystem\Logger\Logger
     */
    protected $logger;
    /**
     * @var SessionManager
     */
    protected $session;
    /**
     * @param \Magento\Framework\App\Helper\Context $context,
     * @param CustomerSession $customerSession,
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
     * @param SellerCollection $sellerCollectionFactory,
     * @param CustomerCollection $customerCollectionFactory,
     * @param RewardproductInterfaceFactory $rewardProductInterface,
     * @param RewardcategoryInterfaceFactory $rewardCategoryInterface,
     * @param RewardproductRepositoryInterface $rewardProductRepository,
     * @param RewardcategoryRepositoryInterface $rewardCategoryRepository,
     * @param RewarddetailInterfaceFactory $rewardDetailInterface,
     * @param RewarddetailRepositoryInterface $rewardDetailRepository,
     * @param RewardRecordCollection $rewardRecordCollection,
     * @param RewardrecordRepositoryInterface $rewardRecordRepository,
     * @param RewardrecordInterfaceFactory $rewardRecordInterface,
     * @param SessionManager $session,
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory,
     * @param \Webkul\MpRewardSystem\Helper\Mail $mailHelper,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date,
     * @param DataObjectHelper $dataObjectHelper,
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
     * @param Session $cart,
     * @param \Webkul\MpRewardSystem\Model\Config\Source\Priority $prioritySet,
     * @param OptionFactory $itemOptionModel,
     * @param \Webkul\Marketplace\Model\ProductFactory $mpproductModel,
     * @param RewardcartCollection $rewardcartCollection,
     * @param RewardProductCollection $rewardProductCollection,
     * @param RewardcategoryCollection $rewardcategoryCollection,
     * @param OrderFactory $orderModel,
     * @param \Magento\Checkout\Model\Cart $cartModel,
     * @param CustomerRepositoryInterface $customerRepository,
     * @param \Magento\Directory\Model\Currency $currency,
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper,
     * @param \Magento\Framework\Pricing\Helper\DataFactory $dataFactory,
     * @param \Webkul\MpRewardSystem\Logger\Logger $logger,
     * @param RewarddetailFactory $rewardDetails
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerSession $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        SellerCollection $sellerCollectionFactory,
        CustomerCollection $customerCollectionFactory,
        RewardproductInterfaceFactory $rewardProductInterface,
        RewardcategoryInterfaceFactory $rewardCategoryInterface,
        RewardproductRepositoryInterface $rewardProductRepository,
        RewardcategoryRepositoryInterface $rewardCategoryRepository,
        RewarddetailInterfaceFactory $rewardDetailInterface,
        RewarddetailRepositoryInterface $rewardDetailRepository,
        RewardRecordCollection $rewardRecordCollection,
        RewardrecordRepositoryInterface $rewardRecordRepository,
        RewardrecordInterfaceFactory $rewardRecordInterface,
        SessionManager $session,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\MpRewardSystem\Helper\Mail $mailHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        Session $cart,
        \Webkul\MpRewardSystem\Model\Config\Source\Priority $prioritySet,
        OptionFactory $itemOptionModel,
        \Webkul\Marketplace\Model\ProductFactory $mpproductModel,
        RewardcartCollection $rewardcartCollection,
        RewardProductCollection $rewardProductCollection,
        RewardcategoryCollection $rewardcategoryCollection,
        OrderFactory $orderModel,
        \Magento\Checkout\Model\Cart $cartModel,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Pricing\Helper\DataFactory $dataFactory,
        \Webkul\MpRewardSystem\Logger\Logger $logger,
        RewarddetailFactory $rewardDetails
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->customerSession = $customerSession;
        $this->objectManager = $objectManager;
        $this->localeCurrency = $localeCurrency;
        $this->_currency = $currency;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->marketplaceHelperData = $marketplaceHelperData;
        $this->sellerCollection = $sellerCollectionFactory;
        $this->rewardProductInterface = $rewardProductInterface;
        $this->rewardCategoryInterface = $rewardCategoryInterface;
        $this->rewardCategoryRepository = $rewardCategoryRepository;
        $this->rewardProductRepository = $rewardProductRepository;
        $this->rewardDetailInterface = $rewardDetailInterface;
        $this->rewardDetailRepository = $rewardDetailRepository;
        $this->rewardRecordCollection = $rewardRecordCollection;
        $this->rewardRecordRepository = $rewardRecordRepository;
        $this->rewardRecordInterface = $rewardRecordInterface;
        $this->customerModel = $customerFactory;
        $this->mailHelper = $mailHelper;
        $this->date = $date;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->priceCurrency = $priceCurrency;
        $this->_cart = $cart;
        $this->prioritySet = $prioritySet;
        $this->itemOptionModel = $itemOptionModel;
        $this->mpProductFactory = $mpproductModel;
        $this->rewardcartCollection = $rewardcartCollection;
        $this->rewardProductCollection = $rewardProductCollection;
        $this->rewardcategoryCollection = $rewardcategoryCollection;
        $this->orderModel = $orderModel;
        $this->cartModel = $cartModel;
        $this->jsonHelper = $jsonHelper;
        $this->customerRepository = $customerRepository;
        $this->customerCollection = $customerCollectionFactory;
        $this->dataFactory = $dataFactory;
        $this->logger = $logger;
        $this->rewardDetails = $rewardDetails;
    }

    /**
     * @return customer id from customer session
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }
    /**
     * get current currency code
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }
    /**
     * get base currency code
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }
    /**
     *
     * @return array
     */
    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }
    /**
     * get currency rate
     * @param  string $currency
     * @param  string $toCurrencies
     * @return float
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }
    /**
     *
     * @param  string $currencycode
     * @return string
     */
    public function getCurrencySymbol($currencycode)
    {
        $currency = $this->localeCurrency->getCurrency($currencycode);

        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }
    /**
     * get formatted currency
     * @param  int $price
     */
    public function getformattedPrice($price)
    {
        return $this->dataFactory->create()->currency($price, true, false);
    }
    /**
     * get formatted currency
     * @param  int $price
     */
    public function getformattedTxt($price)
    {
        return $this->_currency->formatTxt($price);
    }
    /**
     * get amount in base currency amount from current currency
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
     * get allowed currencies list
     * @return []
     */
    public function getAllowedCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }
    /**
     * @return email template
     */
    public function getDefaultTransEmailId()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return email name
     */
    public function getDefaultTransName()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * get reward system is enabled or not for system config.
     * @return module enable or not
     */
    public function getMpRewardEnabled()
    {
        return $this->_scopeConfig->getValue(
            'mprewardsystem/general_settings/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return maximum reward points can Use
     */
    public function getRewardCanUsed()
    {
        return $this->scopeConfig->getValue(
            'mprewardsystem/general_settings/max_reward_used',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return status reward points allowed on registration or not
     */
    public function getAllowRegistration()
    {
        return $this->_scopeConfig->getValue(
            'mprewardsystem/general_settings/allow_registration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return reward points value
     */
    public function getRewardValue($sellerId = 0)
    {
        if (!$sellerId) {
            return $this->_scopeConfig->getValue(
                'mprewardsystem/general_settings/reward_value',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } else {
            return $this->customerModel->create()->load($sellerId)->getRewardprice();
        }
    }
    /**
     * get reward priority
     */
    public function getrewardPriority($sellerId = 0)
    {
        if (!$sellerId) {
            return $this->_scopeConfig->getValue(
                'mprewardsystem/general_settings/priority',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } else {
            return $this->customerModel->create()->load($sellerId)->getRewardPriority();
        }
    }
    /**
     * get status of product quantity wise reward
     */
    public function getrewardQuantityWise($sellerId = 0)
    {

        if (!$sellerId) {
            return $this->_scopeConfig->getValue(
                'mprewardsystem/general_settings/activeproduct',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } else {
            return $this->customerModel->create()->load($sellerId)->getRewardProductStatus();
        }
    }
    /**
     * get reward points on registraion.
     */
    public function getRewardOnRegistration()
    {
        return $this->_scopeConfig->getValue(
            'mprewardsystem/general_settings/registration_reward',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return maximum reward allowed to customer
     */
    public function getAllowedMaxRewardAssign()
    {
        return $this->_scopeConfig->getValue(
            'mprewardsystem/general_settings/max_reward_assign',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return status reward points allowed on Review or not
     */
    public function getAllowReview()
    {
        return $this->_scopeConfig->getValue(
            'mprewardsystem/general_settings/allow_review',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * get reward points on Review.
     */
    public function getRewardOnReview()
    {
        return $this->_scopeConfig->getValue(
            'mprewardsystem/general_settings/review_reward',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * get customer id from customer session.
     */
    public function getPartnerId()
    {
        $partnerId = $this->marketplaceHelperData->getCustomerId();
        return $partnerId;
    }
    /**
     * Get List of Seller Ids
     *
     * @return array
     */
    public function getSellerIdList()
    {
        $sellerIdList = [];
        $collection = $this->sellerCollection
            ->create()
            ->addFieldToFilter('is_seller', 1);
        foreach ($collection as $item) {
            $sellerIdList[] = $item->getSellerId();
        }
        return $sellerIdList;
    }
    /**
     * Get List of Sellers
     *
     * @return array
     */
    public function getSellerList()
    {
        $sellerIdList = $this->getSellerIdList();
        $sellerList = ['0' => 'Admin'];
        $collection = $this->customerCollection
            ->create()
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addFieldToFilter('entity_id', ['in' => $sellerIdList]);
        foreach ($collection as $item) {
            $sellerList[$item->getId()] = $item->getFirstname() . ' ' . $item->getLastname();
        }
        return $sellerList;
    }
    /**
     * set the product reward data
     */
    public function setProductRewardData($rewardProductData)
    {
        $dataObjectProductDetail = $this->rewardProductInterface->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObjectProductDetail,
            $rewardProductData,
            \Webkul\MpRewardSystem\Api\Data\RewardproductInterface::class
        );
        try {
            $this->rewardProductRepository->save($dataObjectProductDetail);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }
    /**
     * set the Category reward data
     */
    public function setCategoryRewardData($rewardCategoryData)
    {
        if ($this->getRewardCartData($rewardCategoryData)->getSize() < 1) {
            unset($rewardCategoryData['entity_id']);
        } else {
            $data = $this->getRewardCartData($rewardCategoryData)->getData();
            $entity_id = $data[0]['entity_id'];
            $rewardCategoryData['entity_id'] = $entity_id;
        }
        $dataObjectCategoryDetail = $this->rewardCategoryInterface->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObjectCategoryDetail,
            $rewardCategoryData,
            \Webkul\MpRewardSystem\Api\Data\RewardcategoryInterface::class
        );
        try {
            $this->rewardCategoryRepository->save($dataObjectCategoryDetail);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }
    /**
     * get the reward cart data
     * @param $rewardCategoryData
     * @return colelction
     */
    public function getRewardCartData($rewardCategoryData)
    {
        $categoryRewardCollection = $this->rewardcategoryCollection
            ->create()
            ->addFieldToFilter('seller_id', ['eq' => $rewardCategoryData['seller_id']])
            ->addFieldToFilter('category_id', ['eq' => $rewardCategoryData['category_id']]);
        return $categoryRewardCollection;
    }
    /**
     * @param $msg
     * @param $adminMsg
     * @param $rewardData
     * set the data from admin
     */
    public function setDataFromAdmin(
        $msg,
        $adminMsg,
        $rewardData
    ) {
        $customerId = isset($rewardData['customer_id']) ? $rewardData['customer_id'] : 0;
        list($assignStatus, $customerReawrdPoints, $allowedMaxReward) = $this->canAssignRewardToCustomer($customerId);
        for ($i = 0; $i < 1; $i++) {
            if (($customerReawrdPoints <= $allowedMaxReward)) {
                $status = $rewardData['status'];
                $rewardValue = $this->getRewardValue();
                $baseCurrencyCode = $this->getBaseCurrencyCode();
                $amount = $rewardValue * $rewardData['points'];
                $recordDetail = [
                    'customer_id' => $rewardData['customer_id'],
                    'seller_id' => $rewardData['seller_id'],
                    'reward_point' => $rewardData['points'],
                    'amount' => $amount,
                    'status' => $status,
                    'action' => $rewardData['type'],
                    'order_id' => $rewardData['order_id'],
                    'item_ids' => isset($rewardData['item_ids']) ? $rewardData['item_ids'] : '',
                    'reward_type' => isset($rewardData['reward_type']) ? $rewardData['reward_type'] : '',
                    'is_revert' => isset($rewardData['is_revert']) ? $rewardData['is_revert'] : 0,
                    'transaction_at' => $this->date->gmtDate(),
                    'currency_code' => $baseCurrencyCode,
                    'curr_amount' => $amount,
                    'review_id' => $rewardData['review_id'],
                    'transaction_note' => $rewardData['note'],
                    'pending_reward' => $rewardData['points'],
                ];
                if (isset($rewardData['register'])) {
                    $recordDetail['pending_reward'] = 0;
                }
                if (isset($rewardData['review_id']) && $rewardData['review_id'] > 0) {
                    $recordDetail['pending_reward'] = 0;
                }
                if ($customerReawrdPoints == $allowedMaxReward && $rewardData['type'] == "credit") {
                    break;
                }
                if ($this->session->getCustomerReward() ==
                    $allowedMaxReward && $rewardData['type'] == "credit") {
                    break;
                }
                /** check the summation of customer reward and the reward get is greater than barrier reward */
                if ((($customerReawrdPoints + $rewardData['points']) > $allowedMaxReward)
                    && $rewardData['type'] == "credit") {
                    $recordDetail['pending_reward'] = ($allowedMaxReward - $customerReawrdPoints);
                    $recordDetail['reward_point'] = $allowedMaxReward - $customerReawrdPoints;
                    $this->session->setCustomerReward($allowedMaxReward);
                }
                if ((($customerReawrdPoints + $rewardData['points']) < $allowedMaxReward)
                    && $rewardData['type'] == "credit" && $rewardData['review_id'] == 0) {
                    if ($this->session->getCustomerReward() + $rewardData['points'] > $allowedMaxReward) {
                        $recordDetail['pending_reward'] = ($allowedMaxReward - $this->session->getCustomerReward());
                        $recordDetail['reward_point'] = $allowedMaxReward - $this->session->getCustomerReward();
                    } else {
                        $recordDetail['pending_reward'] = $rewardData['points'];
                        $recordDetail['reward_point'] = $recordDetail['pending_reward'];
                    }
                    $this->session->setCustomerReward($customerReawrdPoints + $rewardData['points']);
                }
                if ($rewardData['type'] == "debit") {
                    $recordDetail['pending_reward'] = 0;
                }
                if (isset($rewardData['register']) && $rewardData['register'] == 1) {
                    $recordDetail['pending_reward'] = 0;
                }
                $dataObjectRecordDetail = $this->rewardDetailInterface->create();

                $this->dataObjectHelper->populateWithArray(
                    $dataObjectRecordDetail,
                    $recordDetail,
                    \Webkul\MpRewardSystem\Api\Data\RewarddetailInterface::class
                );
                $this->saveDataRewardDetail($dataObjectRecordDetail);

                if ($status == 1) {
                    $this->updateRewardRecordData($msg, $adminMsg, $rewardData);
                }
            }
        }
    }
    /**
     * save the reward detail data
     */
    public function saveDataRewardDetail($completeDataObject)
    {
        try {
            $this->rewardDetailRepository->save($completeDataObject);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }
    /**
     * update table reward if credit
     */
    public function updateRewardDetailsTable($customerId, $sellerid, $orderid, $rewardPoint)
    {
        $dataObjectRecordDetail = $this->rewardDetails->create();
        $data = $dataObjectRecordDetail->getCollection()
            ->addFieldToFilter("order_id", $orderid)
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('seller_id', $sellerid)
            ->addFieldToFilter('action', 'credit');
        $entityId = $data->getData()[0]["entity_id"];
        $rewardDetailModel = $dataObjectRecordDetail
            ->load($entityId)
            ->setRewardPoint($rewardPoint)->save();
    }
    /**
     * get total rewatrd that customer has
     */
    public function getRewardDetails($customerId)
    {
        $data = 0;
        $rewardRecordCollection = $this->rewardRecordCollection->create()
            ->addFieldToSelect("remaining_reward_point")
            ->addFieldToFilter('customer_id', $customerId);
        foreach ($rewardRecordCollection as $key => $value) {
            # code...
            $data += $value->getRemainingRewardPoint();
        }
        return $data;
    }
    /**
     * update reward after invoice
     */
    public function updateRewardRecordData($msg, $adminMsg, $rewardData)
    {
        try {
            $maxPoints = $this->getAllowedMaxRewardAssign();
            $this->session->unsCustomerReward();
            $points = $rewardData['points'];
            $orderid = $rewardData['order_id'];
            $customerId = $rewardData['customer_id'];
            $sellerId = isset($rewardData['seller_id']) ? $rewardData['seller_id'] : 0;
            $entityId = $this->checkAlreadyExists($customerId, $sellerId);
            $totalRemainingReward = $this->getRewardDetails($customerId);
            $remainingPoints = 0;
            $usedPoints = 0;
            $totalPoints = 0;
            $id = '';
            if ($entityId) {
                $rewardRecord = $this->rewardRecordRepository->getById($entityId);
                $remainingPoints = $rewardRecord->getRemainingRewardPoint();
                $usedPoints = $rewardRecord->getUsedRewardPoint();
                $totalPoints = $rewardRecord->getTotalRewardPoint();
                $id = $entityId;
            }
            if (($rewardData['type'] == 'credit') && $totalRemainingReward < $maxPoints) {
                if (($totalRemainingReward + $points) > $this->getAllowedMaxRewardAssign()) {
                    $pointsRem = $this->getAllowedMaxRewardAssign() - $totalRemainingReward;
                    $remainingPoints += $pointsRem;
                    $totalPoints += $pointsRem;
                    $this->updateRewardDetailsTable($customerId, $sellerId, $orderid, $pointsRem);
                } else {
                    $remainingPoints += $points;
                    $totalPoints += $points;
                }
            } elseif ($rewardData['type'] != 'credit') {
                $usedPoints += $points;
                $remainingPoints -= $points;
            }
            $recordData = [
                'customer_id' => $customerId,
                'seller_id' => $sellerId,
                'total_reward_point' => $totalPoints,
                'remaining_reward_point' => $remainingPoints,
                'used_reward_point' => $usedPoints,
                'updated_at' => $this->date->gmtDate(),
            ];
            if ($id) {
                $recordData['entity_id'] = $id;
            }
            $dataObjectRewardRecord = $this->rewardRecordInterface->create();
            $customer = $this->customerModel
                ->create()
                ->load($customerId);
            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $adminEmail = $this->getDefaultTransEmailId();
            $adminUsername = $this->getDefaultTransName();
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            if (!$sellerId) {
                $sellerReceiverInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];
            } else {
                $userdata = $this->customerRepository->getById($sellerId);
                $sellerReceiverInfo = [
                    'name' => $userdata->getFirstname(),
                    'email' => $userdata->getEmail(),
                ];
            }
            $this->dataObjectHelper->populateWithArray(
                $dataObjectRewardRecord,
                $recordData,
                \Webkul\MpRewardSystem\Api\Data\RewardrecordInterface::class
            );
            $this->saveDataRewardRecord($dataObjectRewardRecord);

        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
        $this->mailHelper->sendMail($receiverInfo, $senderInfo, $msg, $remainingPoints);
        $this->mailHelper->sendSellerMail(
            $sellerReceiverInfo,
            $receiverInfo,
            $senderInfo,
            $adminMsg,
            $remainingPoints
        );
    }
    /**
     * @return option array
     */
    public function getPrioritySet()
    {
        return $this->prioritySet->toOptionArray();
    }
    /**
     * save the reward record data
     */
    public function saveDataRewardRecord($completeDataObject)
    {

        $this->rewardRecordRepository->save($completeDataObject);
    }
    /**
     * check already exist data
     * @return row id
     */
    public function checkAlreadyExists($customerId, $sellerId = 0)
    {
        $rowId = 0;
        $rewardRecordCollection = $this->rewardRecordCollection->create()
            ->addFieldToSelect("entity_id")
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('seller_id', $sellerId);
        if ($rewardRecordCollection->getSize()) {
            foreach ($rewardRecordCollection as $rewardRecord) {
                $rowId = $rewardRecord->getEntityId();
            }
        }
        return $rowId;
    }
    /**
     * Priority 0 product based,1 cart based,2 category based
     */
    public function calculateCreditAmountforOrder($orderId = 0)
    {
        $rewardPointDetails = [];
        $sellerWaiseItemData = [];
        if ($orderId != 0) {
            $order = $this->orderModel->create()->load($orderId);
            $cartData = $order->getAllVisibleItems();
        } else {
            $cartData = $this->cartModel->getQuote()->getAllVisibleItems();
        }
        foreach ($cartData as $item) {
            $proid = $item->getProductId();
            $mpassignproductId = 0;
            $itemOption = $this->itemOptionModel->create()
                ->getCollection()
                ->addFieldToFilter('item_id', ['eq' => $item->getId()])
                ->addFieldToFilter('code', ['eq' => 'info_buyRequest']);
            $partnerId = $this->getSellerIdOfItemId($itemOption, $item, $proid);
            $priority = $this->getrewardPriority($partnerId);
            $quantityWise = $this->getrewardQuantityWise($partnerId);
            $categoryIds = $item->getProduct()->getCategoryIds();
            if (!is_array($categoryIds)) {
                $categoryIds = [];
            }
            if ($item->getOrderId() && $item->getOrderId() != 0) {
                $qty = $item->getQtyOrdered();
            } else {
                $qty = $item->getQty();
            }
            $itemPrice = $item->getRowTotal();
            if (empty($sellerWaiseItemData)) {
                array_push(
                    $sellerWaiseItemData,
                    [
                        'seller_id' => $partnerId,
                        'product_id' => [$proid => $qty],
                        'priority' => $priority,
                        'subTotal' => $itemPrice,
                        'item_ids' => $item->getId(),
                        'category_id' => $categoryIds,
                        'quantityWise' => $quantityWise,
                    ]
                );
            } else {
                $flag = true;
                $index = 0;
                foreach ($sellerWaiseItemData as $sellerData) {
                    if ($sellerData['seller_id'] == $partnerId) {
                        $sellerData['product_id'][$proid] = $qty;
                        $sellerData['subTotal'] = $sellerData['subTotal'] + $itemPrice;
                        $sellerData['item_ids'] = $sellerData['item_ids'] . ',' . $item->getId();
                        $sellerData['category_id'] = array_merge_recursive($sellerData['category_id'], $categoryIds);
                        $sellerWaiseItemData[$index] = $sellerData;
                        $flag = false;
                    }
                    ++$index;
                }
                if ($flag == true) {
                    array_push(
                        $sellerWaiseItemData,
                        [
                            'seller_id' => $partnerId,
                            'product_id' => [$proid => $qty],
                            'priority' => $priority,
                            'subTotal' => $itemPrice,
                            'item_ids' => $item->getId(),
                            'category_id' => $categoryIds,
                            'quantityWise' => $quantityWise,
                        ]
                    );
                }
            }
        }
        if (!empty($sellerWaiseItemData)) {
            foreach ($sellerWaiseItemData as $sellerItemData) {
                $sellerId = $sellerItemData['seller_id'];
                $rewardType = 0;
                if (isset($sellerItemData['priority']) && $sellerItemData['priority'] == 0) {
                    //product based
                    $rewardPointDetails[$sellerId]['points'] = $this->getProductData(
                        $sellerItemData,
                        $sellerId
                    );
                } elseif (isset($sellerItemData['priority']) && isset($sellerItemData['subTotal'])
                    && $sellerItemData['priority'] == 1) {
                    $rewardType = 1;
                    //cart based
                    $rewardPointDetails[$sellerId]['points'] = $this->getRewardBasedOnRules(
                        $sellerItemData['subTotal'],
                        $sellerId
                    );
                } elseif ($sellerItemData['priority'] == 2) {
                    $rewardType = 2;
                    //category based
                    if (isset($sellerItemData['category_id'])) {
                        $rewardPointDetails[$sellerId]['points'] =
                        $this->getCategoryData($sellerItemData, $sellerId);
                    }
                }
                $rewardPointDetails[$sellerId]['item_ids'] = $sellerItemData['item_ids'];
                $rewardPointDetails[$sellerId]['reward_type'] = $rewardType;
            }
        }
        return $rewardPointDetails;
    }
    /**
     * give reward point on category
     * @param $sellerItemData
     * @param $sellerId
     * @return reward point
     */
    public function getCategoryData($sellerItemData, $sellerId = 0)
    {
        $rewardpoint = 0;
        $categoryReward = [];
        if (!empty($sellerItemData['category_id'])) {
            $categoryIds = $sellerItemData['category_id'];
            $categoryRewardCollection = $this->rewardcategoryCollection
                ->create()
                ->addFieldToSelect("points")
                ->addFieldToFilter('status', ['eq' => 1])
                ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                ->addFieldToFilter('category_id', ['in' => $categoryIds]);
            if ($categoryRewardCollection->getSize()) {
                foreach ($categoryRewardCollection as $categoryRule) {
                    $categoryReward[] = $categoryRule->getPoints();
                }
                if (!empty($categoryReward)) {
                    $rewardpoint = max($categoryReward);
                }
            }
        }
        return $rewardpoint;
    }
    /**
     * give reward point on product
     * @param $sellerItemData
     * @param $sellerId
     * @return reward point
     */
    public function getProductData($sellerItemData, $sellerId = 0)
    {
        $rewardpoint = 0;
        foreach ($sellerItemData['product_id'] as $productId => $qty) {
            $reward = $this->getProductReward($productId, $sellerId);
            if ($sellerItemData['quantityWise']) {
                $reward = $reward * $qty;
            }
            $rewardpoint += $reward;
        }
        return $rewardpoint;
    }
    /**
     * get seller reward
     * @return seller reward array on seller id
     */
    public function getSellerRewards()
    {
        $customerId = $this->getCustomerId();
        $cart = $this->_cart->getQuote();
        $sellerIds = [];
        foreach ($cart->getAllItems() as $item) {
            $productId = $item->getProduct()->getId();
            $model = $this->marketplaceHelperData->getSellerProductDataByProductId($productId);
            if ($model->getSize()) {
                foreach ($model as $value) {
                    $sellerIds[] = $value->getSellerId();
                }
            } else {
                $sellerIds[] = 0;
            }
        }
        $options = [];
        $collection = $this->rewardRecordInterface->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId])
            ->addFieldToFilter('seller_id', ['in' => $sellerIds]);
        foreach ($collection as $info) {
            if (!isset($options[$info->getSellerId()])) {
                $options[$info->getSellerId()] = [];
                $remainingRewards = $info->getRemainingRewardPoint();
            } else {
                $remainingRewards = $options[$info->getSellerId()]['remaining_reward_point']
                 + $info->getRemainingRewardPoint();
            }
            $options[$info->getSellerId()]['remaining_reward_point'] = $remainingRewards;
            $options[$info->getSellerId()]['amount'] = $remainingRewards * $this->getRewardValue($info->getSellerId());
        }
        return $options;
    }
    /**
     * @param $customerId
     * @return array contain customer reward and max reward
     */
    public function canAssignRewardToCustomer($customerId = 0)
    {
        $customerReawrdPoints = $this->getCustomerTotalReward($customerId);
        $allowedMaxReward = $this->getAllowedMaxRewardAssign();
        if ($allowedMaxReward <= $customerReawrdPoints) {
            return [false, $customerReawrdPoints, $allowedMaxReward];
        }
        return [true, $customerReawrdPoints, $allowedMaxReward];
    }
    /**
     * get Customer total reward ponits
     */
    public function getCustomerTotalReward($customerId = 0)
    {
        $points = 0;
        $rewardRecord = $this->rewardRecordCollection->create()
            ->addFieldToSelect("remaining_reward_point")
            ->addFieldToFilter('customer_id', $customerId);
        foreach ($rewardRecord as $record) {
            $points += $record->getRemainingRewardPoint();
        }
        return $points;
    }
    /**
     * @return seller name
     */
    public function getSellerName($sellerId = 0)
    {
        if ($sellerId) {
            return $this->customerModel->create()->load($sellerId)->getName();
        } else {
            return "Admin";
        }
    }
    /**
     * @return product reward
     */
    public function getProductReward($productId, $sellerId)
    {
        $productCollection = $this->rewardProductCollection->create()
            ->addFieldToSelect("points")
            ->addFieldToFilter('product_id', ['eq' => $productId])
            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
            ->addFieldToFilter('status', ['eq' => 1]);
        $reward = 0;
        if ($productCollection->getSize()) {
            foreach ($productCollection as $productData) {
                if ($productData->getPoints()) {
                    $reward = $productData->getPoints();
                }
            }
        }
        return $reward;
    }
    /**
     * @return reward on cart based rules
     */
    public function getRewardBasedOnRules($amount, $sellerId = 0)
    {
        $today = $this->date->gmtDate('Y-m-d');
        $reward = 0;
        $rewardCartruleCollection = $this->rewardcartCollection
            ->create()
            ->addFieldToSelect("points")
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('start_date', ['lteq' => $today])
            ->addFieldToFilter('end_date', ['gteq' => $today])
            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
            ->addFieldToFilter('amount_from', ['lteq' => $amount])
            ->addFieldToFilter('amount_to', ['gteq' => $amount]);
        if ($rewardCartruleCollection->getSize()) {
            foreach ($rewardCartruleCollection as $cartRule) {
                $reward = $cartRule->getPoints();
            }
        }
        return $reward;
    }
    /**
     * @return seller info on item via ide
     */
    public function getSellerIdOfItemId($itemOption, $item, $productId)
    {
        $partner = 0;
        $optionValue = '';
        if (!empty($itemOption)) {
            foreach ($itemOption as $value) {
                $optionValue = $value->getValue();
            }
        }
        $mpassignproductId = 0;
        if ($optionValue != '') {
            $temp = $this->jsonHelper->jsonDecode($optionValue);
            $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
        }
        if (!$mpassignproductId && $item->getOptions()) {
            foreach ($item->getOptions() as $option) {
                if (isset($option['value']) && is_array($option['value'])) {
                    $temp = $this->jsonHelper->jsonDecode($option['value']);
                }
                if (isset($temp['mpassignproduct_id'])) {
                    $mpassignproductId = $temp['mpassignproduct_id'];
                }
            }
        }
        if ($mpassignproductId) {
            $mpassignModel = $this->objectManager
                ->create(\Webkul\MpAssignProduct\Model\Items::class)->load($mpassignproductId);
            $partner = $mpassignModel->getSellerId();
        } else {
            $sellerProducts = $this->mpProductFactory->create()
                ->getCollection()
                ->addFieldToSelect("seller_id")
                ->addFieldToFilter('mageproduct_id', ['eq' => $productId]);
            foreach ($sellerProducts as $temp) {
                $partner = $temp->getSellerId();
            }
        }
        return $partner;
    }
    /**
     * @return the object of json helper
     */
    public function jsonHelperFunction()
    {
        return $this->jsonHelper;
    }
    /**
     * get Coupon instance.
     *
     * @param int $mpassignproductId
     * @param int $proid
     *
     * @return int
     */
    public function getCouponInstance()
    {
        $couponInstnce = $this->objectManager::getInstance()->create(
            \Webkul\MpSellerCoupons\Model\MpSellerCouponsFactory::class
        );
        return $couponInstnce;
    }
}
