<?php
/**
 * MpWholesale Helper
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Helper;

use Magento\Customer\Model\CustomerFactory;
use Magento\User\Model\UserFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MODULE_STATUS                        = 'mpwholsale/general_settings/status';
    const POLICY_DESCRIPTION                   = 'mpwholsale/privacy_policy/policy_content';
    const POLICY_HEADING                       = 'mpwholsale/privacy_policy/policy_heading';
    const ADMIN_EMAIL                          = 'mpwholsale/general_settings/adminemail';
    const ADMIN_NAME                           = 'mpwholsale/general_settings/name';
    const WHOLESALER_APPROVAL_REQUIRED         = 'mpwholsale/general_settings/wholesaler_approval';
    const WHOLESALER_PRODUCT_APPROVAL_REQUIRED = 'mpwholsale/general_settings/wholeseller_product_approval';
    const RULE_ENABLE_STATUS                   = 1;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelperData;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;
    /**
     * @var CustomerFactory
     */
    protected $customerModel;
    /**
     * @var UserFactory
     */
    protected $adminUserModel;

    /**
     * @var Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData
     * @param \Webkul\MpWholesale\Model\PriceRuleFactory $priceRuleFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Webkul\MpWholesale\Model\ProductFactory $wholeSaleProductModel
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param CustomerFactory $customerModel
     * @param UserFactory $adminUserModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        \Webkul\MpWholesale\Model\PriceRuleFactory $priceRuleFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Webkul\MpWholesale\Model\ProductFactory $wholeSaleProductModel,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        CustomerFactory $customerModel,
        UserFactory $adminUserModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->_scopeConfig             = $context->getScopeConfig();
        $this->_marketplaceHelperData   = $marketplaceHelperData;
        $this->priceRuleFactory         = $priceRuleFactory;
        $this->authSession              = $authSession;
        $this->filterProvider           = $filterProvider;
        $this->wholeSaleProductModel    = $wholeSaleProductModel;
        $this->pricingHelper            = $pricingHelper;
        $this->localeCurrency           = $localeCurrency;
        $this->customerModel            = $customerModel;
        $this->adminUserModel           = $adminUserModel;
        $this->productFactory           = $productFactory;
        $this->currency                 = $currency;
        $this->storeManager             = $storeManager;
        $this->jsonHelper               = $jsonHelper;
    }

    /**
     * getModuleStatus
     * @return bool
     */
    public function getModuleStatus()
    {
        return  $this->_scopeConfig->getValue(
            self::MODULE_STATUS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * getPolicyDescription
     * @return string
     */
    public function getPolicyDescription()
    {
        return  $this->_scopeConfig->getValue(
            self::POLICY_DESCRIPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * getHedaingData
     * @return string
     */
    public function getHedaingData()
    {
        return  $this->_scopeConfig->getValue(
            self::POLICY_HEADING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getAdminEmail
     * @return string|null
     */
    public function getAdminEmail()
    {
        return  $this->_scopeConfig->getValue(
            self::ADMIN_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * getAdminName
     * @return string|null
     */
    public function getAdminName()
    {
        return  $this->_scopeConfig->getValue(
            self::ADMIN_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /*
    * return base currency symbol
    */
    public function getBaseCurrencySymbol()
    {
        return $this->localeCurrency->getCurrency(
            $this->getBaseCurrencyCode()
        )->getSymbol();
    }

    /*
    * return current currency symbol
    */
    public function getCurrentCurrencyCodesymbol()
    {
        return $this->localeCurrency->getCurrency(
            $this->getCurrentCurrencyCode()
        )->getSymbol();
    }

    // return currency currency code
    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    // get base currency code
    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }
    /**
     * get customer data by customer id
     *
     * @param integer
     * @return object
     */
    public function getCustomerData($customerId)
    {
        return $this->customerModel->create()->load($customerId);
    }

    /**
     * get wholesaler user data by user id
     *
     * @param integer
     * @return object
     */
    public function getWholesalerData($userId)
    {
        return $this->adminUserModel->create()->load($userId);
    }

    /**
     * isWholesalerApprovalRequired
     * @return boolean
     */
    public function isWholesalerApprovalRequired()
    {
        return  $this->_scopeConfig->getValue(
            self::WHOLESALER_APPROVAL_REQUIRED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [isWholesalerProductApprovalRequired to check wholesaler product's approval reuired or not]
     * @return boolean
     */
    public function isWholesalerProductApprovalRequired()
    {
        return  $this->_scopeConfig->getValue(
            self::WHOLESALER_PRODUCT_APPROVAL_REQUIRED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // load product by product id
    public function getProduct($productId)
    {
        return $this->productFactory->create()->load($productId);
    }

    /**
     * get current admin user data
     * @return mixed
     */
    public function getCurrentUser()
    {
        return $this->authSession->getUser();
    }

    /**
     * get Formatted price show
     *
     * @param float $price
     * @return mixed
     */
    public function getformattedPrice($price)
    {
        return $this->pricingHelper
            ->currency($price, true, false);
    }

    /**
     * get base currency price from current currency
     * @param [float] $price
     * @return float
     */
    public function getBaseCurrencyPrice($price, $from = null)
    {
        if (!$from) {
        /*
        * Get Current Store Currency Rate
        */
            $currentCurrencyCode = $this->getCurrentCurrencyCode();
        } else {
            $currentCurrencyCode = $from;
        }
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }
        return $price / $rates[$currentCurrencyCode];
    }

    // get all allowed currency in system config
    public function getConfigAllowCurrencies()
    {
        return $this->currency->getConfigAllowCurrencies();
    }

    /**
     * Retrieve currency rates to other currencies.
     *
     * @param string     $currency
     * @param array|null $toCurrencies
     *
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        // give the currency rate
        return $this->currency->getCurrencyRates($currency, $toCurrencies);
    }

    /**
     * Get List of Price Rule for the Current Wholesaler
     *
     * @return array
     */
    public function getRulesList()
    {
        $userId = $this->getCurrentUser()->getUserId();
        $rulesList[] = [];
        $collection = $this->priceRuleFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter('user_id', ['eq'=>$userId])
                            ->addFieldToFilter('status', ['eq' => self::RULE_ENABLE_STATUS]);
        foreach ($collection as $item) {
            $rulesList[] = ['value'=>$item->getEntityId(),'label' =>$item->getRuleName()];
        }
        return $rulesList;
    }

    //filter content to get the media files
    public function getFilterContent()
    {
        $content = $this->getPolicyDescription();
        return $this->filterProvider->getPageFilter()->filter($content);
    }

    public function isWholeSalerExistForProduct($productId)
    {
        $wholeSaleProductModel = $this->wholeSaleProductModel->create()
                                 ->getCollection()
                                 ->addFieldToFilter('product_id', $productId)
                                 ->addFieldToFilter('status', 1);
        if ($wholeSaleProductModel->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonEncodeData($data)
    {
        return $this->jsonHelper->jsonEncode($data, true);
    }
}
