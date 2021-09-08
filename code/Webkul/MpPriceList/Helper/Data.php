<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Helper;

use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public $printPreQuery = false;

    public $printPostQuery = false;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Webkul\MpPriceList\Model\RuleFactor
     */
    protected $priceRule;

    /**
     * @var \Webkul\MpPriceList\Model\AssignedRuleFactory
     */
    protected $assignedRule;

    /**
     * @var \Webkul\MpPriceList\Model\DetailsFactory
     */
    protected $userDetails;

    /**
     * @var \Webkul\MpPriceList\Model\PriceListFactory
     */
    protected $priceList;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

  /**
   * @param \Magento\Framework\App\Helper\Context $context
   * @param \Magento\Framework\App\ResourceConnection $resource
   * @param \Magento\Checkout\Model\CartFactory $cart
   * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
   * @param \Webkul\MpPriceList\Model\ItemsFactory $items
   * @param \Magento\Framework\App\Http\Context $httpContext
   * @param \Webkul\MpPriceList\Model\RuleFactory $priceRule
   * @param \Webkul\MpPriceList\Model\AssignedRuleFactory $assignedRule
   * @param \Webkul\MpPriceList\Model\DetailsFactory $userDetails
   * @param \Webkul\MpPriceList\Model\PriceListFactory $priceList
   * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
   * @param \Magento\Customer\Model\SessionFactory $customerSession
   * @param StoreManagerInterface $storeManager
   * @param \Magento\Directory\Model\Currency $currency
   * @param \Magento\Directory\Helper\Data $directoryHelper
   */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Checkout\Model\CartFactory $cart,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Webkul\MpPriceList\Model\ItemsFactory $items,
        \Magento\Framework\App\Http\Context $httpContext,
        \Webkul\MpPriceList\Model\RuleFactory $priceRule,
        \Webkul\MpPriceList\Model\AssignedRuleFactory $assignedRule,
        \Webkul\MpPriceList\Model\DetailsFactory $userDetails,
        \Webkul\MpPriceList\Model\PriceListFactory $priceList,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Cache\ManagerFactory $cacheManagerFactory
    ) {
        $this->_resource = $resource;
        $this->_cart = $cart;
        $this->_timezone = $timezone;
        $this->_items = $items;
        $this->httpContext = $httpContext;
        $this->priceRule = $priceRule;
        $this->assignedRule = $assignedRule;
        $this->userDetails = $userDetails;
        $this->priceList = $priceList;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->customerSession = $customerSession;
        $this->_storeManager   =   $storeManager;
        $this->_currency  =   $currency;
        $this->_directoryHelper =   $directoryHelper;
        $this->cacheManager = $cacheManagerFactory;
        parent::__construct($context);
    }

    /**
     * get price
     *
     * @param array $product
     * @param double $price
     * @return void
     */
    public function getPrice($product, $price)
    {
        $ruleDetails = $this->getPreAppliedRuleDetails($product);
        $this->clearCache();
        if ($this->checkRuleToBeApplied($ruleDetails, $price)) {
            return $this->getFinalPrice($ruleDetails, $price);
        }
        return $price;
    }
    
    /**
     * Get Original Price of Product
     *
     * @param array $product
     * @param int] $price
     * @param array $ruleDetails
     * @return void
     */
    public function getOriginalPrice($product, $price, $ruleDetails)
    {
        if ($ruleDetails['rule_id'] > 0) {
            $amount = $ruleDetails['amount'];
            if ($ruleDetails['fixed']) {
                if ($ruleDetails['discount']) {
                    $price = $price + $amount;
                } else {
                    $price = $price - $amount;
                }
            } else {
                if ($ruleDetails['discount']) {
                    $price = ($price * 100)/(100 - $amount);
                } else {
                    $price = ($price * 100)/(100 + $amount);
                }
            }
        }
        return $price;
    }

    /**
     * post applied rules information
     *
     * @param array $product
     * @param int $qty
     * @param double $total
     * @param boolean $exclude
     * @return array
     */
    public function getPostAppliedRuleDetails($product, $qty, $total, $exclude = false)
    {
        $details = ['amount' => 0, 'rule_id' => 0, "discount" => true, 'fixed' => true, 'sellerId' => 0];
        $customerId = 0;
        $customerGroupId = 0;
        $customerSession = $this->customerSession->create();
        if (!empty($customerSession) && !empty($customerSession->getCustomerId())) {
            $customerId = $customerSession->getCustomerId();
            $customerGroupId = $customerSession->getCustomerGroupId();
        }
       
        $categoyIds = $product->getCategoryIds();
        $collection = $this->_items->create()->getCollection();
        $collection = $this->joinRules($collection);
        $collection = $this->joinAssignedRules($collection);
        $collection = $this->joinUser($collection);
        $collection = $this->joinPriceList($collection);
        $sellerCollection = $this->marketplaceHelper->getSellerProductDataByProductId($product->getId());
        if (!empty($sellerCollection)) {
            $sellerId = $sellerCollection->getFirstItem()->getSellerId();
        }
        $today = $this->_timezone->date()->format('Y-m-d');
        $sql = '(';
        if ($exclude) {
            $sql .= '(main_table.entity_type= 3 and main_table.entity_value <= '.$qty.')';
            $sql .= ' or (main_table.entity_type= 4 and main_table.entity_value <= '.$total.')';
            $sql .= ')';
            $sql .= ' and (';
            $sql .= '(pricelist.start_date <="'.$today.'" and pricelist.end_date >="'.$today.'") and';
            $sql .= ' rule.status = 1 and pricelist.status = 1';
            $sql .= ' and rule.is_combination=0';
            $sql .= ' and ((user.type = 1 and user.user_id = '.$customerId.') or
                    (user.type = 2 and user.user_id = '.$customerGroupId.'))';
            $sql .= ')';
        } else {
            $sql .= '(main_table.entity_type= 1 and main_table.entity_value = '.$product->getId().')';
            foreach ($categoyIds as $categoyId) {
                $sql .= ' or (main_table.entity_type= 2 and main_table.entity_value = '.$categoyId.')';
            }
            $sql .= ' or (main_table.entity_type= 3 and main_table.entity_value <= '.$qty.')';
            $sql .= ' or (main_table.entity_type= 4 and main_table.entity_value <= '.$total.')';
            $sql .= ')';
            $sql .= ' and (';
            $sql .= '(pricelist.start_date <="'.$today.'" and pricelist.end_date >="'.$today.'") and';
            $sql .= ' rule.status = 1 and pricelist.status = 1';
            if (!empty($sellerId)) {
                $sql .= ' and pricelist.seller_id = '.$sellerId;
            } else {
                $sql .= ' and pricelist.seller_id = 0';
            }
            $sql .= ' and rule.is_combination=0';
            $sql .= ' and ((user.type = 1 and user.user_id = '.$customerId.') or
                    (user.type = 2 and user.user_id = '.$customerGroupId.'))';
            $sql .= ')';
        }

        $sqlQuery = $sql;
        $priceListCollection = $collection;
        $size = $priceListCollection->getSize();

        $collection->getSelect()->where($sql);
        $collection->getSelect()->order('pricelist.priority', 'ASC');
        $collection->getSelect()->order('rule.priority', 'ASC');
        $collection->getSelect()->order('rule.id', 'ASC');
        $collection->getSelect()->order('main_table.id', 'ASC');
        $collection->getSelect()->limit(1);
        if (($collection->getSize()) && !empty($sellerId)) {
            foreach ($collection as $item) {
                $item->getPricelistPriority();
                $calculationType = $item->getCalculationType();
                $priceType = $item->getPriceType();
                $amount = $item->getAmount();
                if ($priceType == 2) { // percent amount
                    $details['fixed'] = false;
                }
                if ($calculationType == 1) { // increase price
                    $details['discount'] = false;
                }
                $details['rule_id'] = $item->getRuleId();
                $details['amount'] = $amount;
                $details['rule_priority'] = $item->getRulePriority();
                $details['pricelist_priority'] = $item->getPricelistPriority();
                $details['sellerId'] = $item->getSellerId();
            }
        }
        if ($details['rule_id'] == 0) {
            $details = $this->getGlobalPostAppliedRule($sqlQuery, $priceListCollection, $details);
        }
        return $details;
    }

    /**
     * get global pre applied rule
     * of admin
     *
     * @param string $sqlQuery
     * @param object $priceListCollection
     * @param array $details
     * @return array
     */
    public function getGlobalPostAppliedRule($sqlQuery, $priceListCollection, $details)
    {
        $priceListCollection->getSelect()->reset(\Zend_Db_Select::WHERE);
        $find   = 'and pricelist.seller_id = ';
        $pos = strpos($sqlQuery, $find);
        $len = strlen($find);
        $start = $len + $pos;
        $spacePos = strpos($sqlQuery, ' ', $start);
        $toBeReplaced = substr($sqlQuery, $pos, ($spacePos-$pos));
        $equalPos = strpos($toBeReplaced, "= ");
        $newString = substr_replace($toBeReplaced, 0, $equalPos+2);
        $sql = str_replace($toBeReplaced, $newString, $sqlQuery);
        $priceListCollection->getSelect()->where($sql);
        if ($priceListCollection->getSize()) {
            $collectionArray = $priceListCollection->getData();
            foreach ($collectionArray as $item) {
                $applicableOn = $item['apply_on'];
                if (($applicableOn == 1) || ($applicableOn == 2)) {
                    continue;
                }
                $calculationType = $item['calculation_type'];
                $priceType = $item['price_type'];
                $amount = $item['amount'];
                if ($priceType == 2) {
                    $details['fixed'] = false;
                }
                if ($calculationType == 1) {
                    $details['discount'] = false;
                }
                $details['rule_id'] = $item['rule_id'];
                $details['amount'] = $amount;
                $details['rule_priority'] = $item['rule_priority'];
                $details['pricelist_priority'] = $item['pricelist_priority'];
            }
        }
        return $details;
    }

    /**
     * query on rules collection
     *
     * @param array $collection
     * @return void
     */
    public function joinRules($collection)
    {
        $joinTable = $this->_resource->getTableName('wk_mp_pricelist_rule');
        $sql = 'main_table.parent_id = rule.id';
        $fields = ['calculation_type', 'price_type', 'amount', 'status', 'priority as rule_priority', 'apply_on'];
        $collection->getSelect()->join($joinTable.' as rule', $sql, $fields);
        $collection->addFilterToMap('id', 'main_table.id');
        return $collection;
    }

    /**
     * join assigned rules in collection
     *
     * @param array $collection
     * @return void
     */
    public function joinAssignedRules($collection)
    {
        $joinTable = $this->_resource->getTableName('wk_mp_pricelist_assigned_rule');
        $sql = 'main_table.parent_id = assigned_rule.rule_id';
        $fields = ['pricelist_id', 'rule_id'];
        $collection->getSelect()->join($joinTable.' as assigned_rule', $sql, $fields);
        return $collection;
    }

    /**
     * join user
     *
     * @param array $collection
     * @return void
     */
    public function joinUser($collection)
    {
        $joinTable = $this->_resource->getTableName('wk_mp_pricelist_user_details');
        $sql = 'assigned_rule.pricelist_id = user.pricelist_id';
        $fields = ['pricelist_id', 'user_id', 'type'];
        $collection->getSelect()->join($joinTable.' as user', $sql, $fields);
        return $collection;
    }

    /**
     * join queries
     *
     * @param array $collection
     * @return void
     */
    public function joinPriceList($collection)
    {
        $joinTable = $this->_resource->getTableName('wk_mp_pricelist_list');
        $sql = 'pricelist.id = user.pricelist_id';
        $fields = ['priority as pricelist_priority', 'status', 'seller_id'];
        $collection->getSelect()->join($joinTable.' as pricelist', $sql, $fields);
        return $collection;
    }

    /**
     * product filter in query
     *
     * @param int $product
     * @return void
     */
    public function getProductFilterQuery($product)
    {
        $sql = '(main_table.entity_type= 1 and main_table.entity_value = '.$product->getId().')';
        return $sql;
    }

    /**
     * category filter
     *
     * @param int $product
     * @return string
     */
    public function getCategoryFilterQuery($product)
    {
        $categoyIds = $product->getCategoryIds();
        $sql = [];
        foreach ($categoyIds as $categoyId) {
            $sql[] = '(main_table.entity_type= 2 and main_table.entity_value = '.$categoyId.")";
        }
        $sql = implode(" or ", $sql);
        return $sql;
    }

    /**
     * add filter on pricelist/rule status
     *
     * @return void
     */
    public function getStatusFilterQuery()
    {
        $sql = ' rule.status = 1 and pricelist.status = 1';
        return $sql;
    }

    /**
     * pre applied rules
     *
     * @param array $product
     * @param boolean $excludeCartRule
     * @return array
     */
    public function getPreAppliedRuleDetails($product, $excludeCartRule = false)
    {
        $total = (float) $product->getData("price");
        $details = ['amount' => 0, 'rule_id' => 0, "discount" => true, 'fixed' => true, 'sellerId' => 0];
        $customerId = 0;
        $customerGroupId = 0;
        $customerSession = $this->customerSession->create();
        if (!empty($customerSession) && !empty($customerSession->getCustomerId())) {
            $customerId = $customerSession->getCustomerId();
            $customerGroupId = $customerSession->getCustomerGroupId();
        }
        $categoyIds = $product->getCategoryIds();
        $collection = $this->_items->create()->getCollection();
        $collection = $this->joinRules($collection);
        $collection = $this->joinAssignedRules($collection);
      
        $collection = $this->joinUser($collection);
        $collection = $this->joinPriceList($collection);
        $sellerCollection = $this->marketplaceHelper->getSellerProductDataByProductId($product->getId());
        if (!empty($sellerCollection)) {
            $sellerId = $sellerCollection->getFirstItem()->getSellerId();
        }
     
        $today = $this->_timezone->date()->format('Y-m-d');
        $sql = "(";
        $sql .= '(main_table.entity_type= 1 and main_table.entity_value = '.$product->getId().")";
        foreach ($categoyIds as $categoyId) {
            $sql .= ' or (main_table.entity_type= 2 and main_table.entity_value = '.$categoyId.")";
        }
        if (!$excludeCartRule) {
            $sql .= ' or (main_table.entity_type= 3 and main_table.entity_value = 1)';
            $sql .= ' or (main_table.entity_type= 4 and main_table.entity_value <= '.$total.')';
        }
        $sql .= ")";
        $sql .= ' and (';
        $sql .= '(pricelist.start_date <="'.$today.'" and pricelist.end_date >="'.$today.'") and';
        $sql .= ' rule.status = 1 and pricelist.status = 1 ';
        if (!empty($sellerId)) {
            $sql .= 'and pricelist.seller_id = '.$sellerId;
        } else {
            $sql .= 'and pricelist.seller_id = 0';
        }

        $sql .= ' and rule.is_combination=0';
        $sql .= ' and ((user.type = 1 and user.user_id = '.$customerId.') or
                (user.type = 2 and user.user_id = '.$customerGroupId.'))';
        $sql .= ')';
        
        $sqlQuery = $sql;
        $priceListCollection = $collection;
        $size = $priceListCollection->getSize();
        $collection->getSelect()->where($sql);
        $collection->getSelect()->order('pricelist.priority', 'ASC');
        $collection->getSelect()->order('rule.priority', 'ASC');
        $collection->getSelect()->order('rule.id', 'ASC');
        $collection->getSelect()->order('main_table.id', 'ASC');
        $collection->getSelect()->group('main_table.parent_id');
        $collection->getSelect()->limit(1);
        if ($collection->getSize() && !empty($sellerId)) {
            foreach ($collection as $item) {
                $applicableOn = $item->getApplyOn();
                if (($applicableOn == 3) || ($applicableOn == 4)) {
                    continue;
                }
                $item->getPricelistPriority();
                $calculationType = $item->getCalculationType();
                $priceType = $item->getPriceType();
                $amount = $item->getAmount();
                if ($priceType == 2) {
                    $details['fixed'] = false;
                }
                if ($calculationType == 1) {
                    $details['discount'] = false;
                }
                $details['rule_id'] = $item->getRuleId();
                $details['amount'] = $amount;
                $details['rule_priority'] = $item->getRulePriority();
                $details['pricelist_priority'] = $item->getPricelistPriority();
                $details['sellerId'] = $item->getSellerId();
            }
        }
        if ($details['rule_id'] == 0) {
            $details = $this->getGlobalPreAppliedRule($sqlQuery, $priceListCollection, $details);
        }
        return $details;
    }

    /**
     * get global pre applied rule
     * of admin
     *
     * @param string $sqlQuery
     * @param object $priceListCollection
     * @param array $details
     * @return array
     */
    public function getGlobalPreAppliedRule($sqlQuery, $priceListCollection, $details)
    {
        $priceListCollection->getSelect()->reset(\Zend_Db_Select::WHERE);
        $find   = 'and pricelist.seller_id = ';
        $pos = strpos($sqlQuery, $find);
        $len = strlen($find);
        $start = $len + $pos;
        $spacePos = strpos($sqlQuery, ' ', $start);
        $toBeReplaced = substr($sqlQuery, $pos, ($spacePos-$pos));
        $equalPos = strpos($toBeReplaced, "= ");
        $newString = substr_replace($toBeReplaced, 0, $equalPos+2);
        $sql = str_replace($toBeReplaced, $newString, $sqlQuery);
        
        $priceListCollection->getSelect()->where($sql);
        if ($priceListCollection->getSize()) {
            $collectionArray = $priceListCollection->getData();
            foreach ($collectionArray as $item) {
                $applicableOn = $item['apply_on'];
                if (($applicableOn == 3) || ($applicableOn == 4)) {
                    continue;
                }
                $calculationType = $item['calculation_type'];
                $priceType = $item['price_type'];
                $amount = $item['amount'];
                if ($priceType == 2) {
                    $details['fixed'] = false;
                }
                if ($calculationType == 1) {
                    $details['discount'] = false;
                }
                $details['rule_id'] = $item['rule_id'];
                $details['amount'] = $amount;
                $details['rule_priority'] = $item['rule_priority'];
                $details['pricelist_priority'] = $item['pricelist_priority'];
            }
        }
        return $details;
    }

    /**
     * calculation type options
     *
     * @return array
     */
    public function getCalculationTypeOptions()
    {
        $options = [];
        $options[1] = __('Increase Price');
        $options[2] = __('Decrease Price');
        return $options;
    }

    /**
     * price type options array
     *
     * @return array
     */
    public function getPriceTypeOptions()
    {
        $options = [];
        $options[1] = __('Fixed Price');
        $options[2] = __('Percent Price');
        return $options;
    }

    /**
     * return status array
     *
     * @return array
     */
    public function getAdminGridStatusOptions()
    {
        $options = [];
        $options[1] = __('Active');
        $options[2] = __('Inactive');
        return $options;
    }

    /**
     * return object manager instance
     *
     * @return array
     */
    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * custom price option
     *
     * @param array $item
     * @param double $price
     * @return void
     */
    public function getCustomOptionPrice($item, $price)
    {
        $multipleOption = 'false';
        $customOptionPrice = 0;
        $product = $item->getProduct();
        $customOptions = $item->getProduct()
                            ->getTypeInstance(true)
                            ->getOrderOptions($item->getProduct());
        $infoBuyRequest = $customOptions['info_buyRequest'];
        try {
            if (!array_key_exists("options", $infoBuyRequest)) {
                return $customOptionPrice;
            }
                $selectedOptions = $infoBuyRequest['options'];
            foreach ($product->getOptions() as $option) {
                $optionId = $option->getOptionId();
                if (array_key_exists($optionId, $selectedOptions)) {
                    if (trim($selectedOptions[$optionId]) == "") {
                        continue;
                    }
                    $optionType = $option->getType();
                    $singleValueOptions = ['drop_down', 'radio'];
                    $multiValueOptions = ['checkbox', 'multiple'];
                    if (in_array($optionType, $singleValueOptions)) {
                        $values = $option->getValues();
                        $customOptionPrice = $this->getCustomPriceBasedOnCondition(
                            $values,
                            $selectedOptions[$optionId],
                            $price,
                            $multipleOption
                        );
                    } elseif (in_array($optionType, $multiValueOptions)) {
                        $multipleOption = true;
                        $values = $option->getValues();
                        $customOptionPrice = $this->getCustomPriceBasedOnCondition(
                            $values,
                            $selectedOptions[$optionId],
                            $price,
                            $multipleOption
                        );
                    } else {
                        $priceType = $option->getPriceType();
                        $optionPrice = $option->getPrice();
                        if ($priceType != "fixed") {
                            $optionPrice = ($price*$optionPrice)/100;
                        }
                        $customOptionPrice += $optionPrice;
                    }
                }
            }
        } catch (\Exception $e) {
            return $customOptionPrice;
        }
        return $customOptionPrice;
    }

    /**
     * custom price based on condition
     *
     * @param array $values
     * @param int $optionsId
     * @param double $price
     * @param boolean $multipleOption
     * @return double
     */
    public function getCustomPriceBasedOnCondition($values, $optionsId, $price, $multipleOption)
    {
        $customOptionPrice = 0;
        try {
            foreach ($values as $value) {
                $compareIfCondition = $value->getOptionTypeId() == $optionsId;
                if ($multipleOption) {
                    $compareIfCondition = in_array($value->getOptionTypeId(), $selectedOptions[$optionId]);
                }
                if ($compareIfCondition) {
                    $priceType = $value->getPriceType();
                    $optionPrice = $value->getPrice();
                    if ($priceType != "fixed") {
                        $optionPrice = ($price*$optionPrice)/100;
                    }
                    $customOptionPrice += $optionPrice;
                }
            }
            return $customOptionPrice;
        } catch (\Exception $e) {
            return $customOptionPrice;
        }
    }

    /**
     * process product price
     *
     * @param array $item
     * @param boolean $parentItem
     * @param boolean $useParent
     * @param boolean $excludeCartRule
     * @return array
     */
    public function processItemPrice($item, $parentItem = false, $useParent = false, $excludeCartRule = false)
    {
        $result = ['updated' => false];
        try {
            $baseCurrencyCode = $this->getBaseCurrencyCode();
            $storeCurrencyCode =  $this->getCurrentCurrencyCode();
            $preRuleApplied = false;
            $postRuleApplied = false;
            $cartItemPrice = $this->getOriginalPriceByItem($item);
            $originalCartItemPrice = $cartItemPrice;
            $product = $item->getProduct();
            $qty = $item->getQty();
            if ($useParent) {
                $qty = $parentItem->getQty();
            }
            $price = $product->getData("price");
            $originalCustomOptionPrice = $this->getCustomOptionPrice($item, $price);
            $preAppliedDetails = $this->getPreAppliedRuleDetails($product, $excludeCartRule);
            if ($this->checkRuleToBeApplied($preAppliedDetails, $price)) {
                if ($preAppliedDetails['rule_id'] > 0) {
                    $priceAfterRule = $this->getFinalPrice($preAppliedDetails, $price);
                    $customOptionPrice = $this->getCustomOptionPrice($item, $priceAfterRule);
                    if ($preAppliedDetails['discount']) {
                        $differnce = $originalCustomOptionPrice - $customOptionPrice;
                        $differnce += $price - $priceAfterRule;
                        $originalCartItemPrice = $cartItemPrice + $differnce;
                    } else {
                        $differnce = $customOptionPrice - $originalCustomOptionPrice;
                        $differnce += $priceAfterRule - $price;
                        $originalCartItemPrice = $cartItemPrice - $differnce;
                    }
                    $preRuleApplied = true;
                }
            }
            $total = $originalCartItemPrice * $qty;
            $postAppliedDetails = $this->getPostAppliedRuleDetails($product, $qty, $total);
            if ($postAppliedDetails['rule_id'] > 0) {
                if (((int) $postAppliedDetails['sellerId'] > 0) && ((int) $preAppliedDetails['sellerId'] == 0)) {
                    $preRuleApplied = false;
                }
                $postRuleApplied = $this->checkPostRuleAppliedStatus(
                    $postAppliedDetails,
                    $preRuleApplied,
                    $preAppliedDetails
                );
            }

            if ($this->checkRuleToBeApplied($postAppliedDetails, $price)) {
                if ($postRuleApplied) {
                    if ($preRuleApplied) { // need recalculation
                        if ($postRuleApplied['discount']) {
                            $differnce = $originalCustomOptionPrice - $customOptionPrice;
                            $differnce += $price - $priceAfterRule;
                            $originalCartItemPrice = $cartItemPrice + $differnce;
                        } else {
                            $differnce = $customOptionPrice - $originalCustomOptionPrice;
                            $differnce += $priceAfterRule - $price;
                            $originalCartItemPrice = $cartItemPrice - $differnce;
                        }
                        $extraPrice = $originalCartItemPrice - $originalCustomOptionPrice - $price;
                    } else {
                        $extraPrice = $originalCartItemPrice - $originalCustomOptionPrice - $price;
                    }
                    $finalPrice = $this->getFinalPrice($postAppliedDetails, $price);
                    $finalCustomOptionPrice = $this->getCustomOptionPrice($item, $finalPrice);
                    $totalPrice = $extraPrice + $finalPrice + $finalCustomOptionPrice;
                    if ($baseCurrencyCode != $storeCurrencyCode) {
                        $totalPrice =  $this->getwkconvertCurrency($baseCurrencyCode, $storeCurrencyCode, $totalPrice);
                    }
                    $item->setCustomPrice($totalPrice);
                    $item->setOriginalCustomPrice($totalPrice);
                    $item->setRowTotal($item->getQty()*$totalPrice);
                    $item->getProduct()->setIsSuperMode(true);
                    $item->save();
                    $result['updated'] = true;
                    $result['price'] = $totalPrice;
                    return $result;
                }
            }
            $result['price'] = $cartItemPrice;
            if ($result['updated'] == false) {
                $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productData = $_objectManager->get('Magento\Quote\Model\Quote\Item')->load($item->getId());
                 
                $product_id=$productData->getProductId();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $product = $objectManager->get('Magento\Catalog\Model\Product')->load($product_id);

                if($product->getAuctionType() != 2){
                    $item->setCustomPrice(null);
                    $item->setOriginalCustomPrice(null);
                    $item->setRowTotal($item->getQty()*$cartItemPrice);
                    $item->getProduct()->setIsSuperMode(false);
                    $item->save();
                }
                
            }
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }

    /**
     * check rule to be applied or not
     *
     * @param array $ruleDetails
     * @param float $price
     * @return boolean
     */
    public function checkRuleToBeApplied($ruleDetails, $price)
    {
        if ($ruleDetails['rule_id'] > 0) {
            $amount = $ruleDetails['amount'];
            if ($ruleDetails['discount'] && $ruleDetails['fixed']) {
                if ($ruleDetails['amount'] > $price) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }
    
    /**
     * check post rule status
     *
     * @param array $postAppliedDetails
     * @param boolean $preRuleApplied
     * @param array $preAppliedDetails
     * @return boolean
     */
    public function checkPostRuleAppliedStatus($postAppliedDetails, $preRuleApplied, $preAppliedDetails)
    {
        try {
            if ($preRuleApplied) {
                if ($postAppliedDetails['rule_id'] != $preAppliedDetails['rule_id']) {
                    if ($postAppliedDetails['pricelist_priority'] <= $preAppliedDetails['pricelist_priority']) {
                        if ($postAppliedDetails['rule_priority'] < $preAppliedDetails['rule_priority']) {
                            $postRuleApplied = true;
                        }
                    }
                }
            } else {
                $postRuleApplied = true;
            }
            return $postRuleApplied;
        } catch (\Exception $e) {
            return $postRuleApplied = false;
        }
    }

    /**
     * Collect Totals
     *
     * @param object $quote
     */
    public function collectTotals($quote)
    {
        $itemDetails = [];
        $configItemIds = [];
        $bundleItemIds = [];
        $hasBundleProduct = false;
        $hasConfigProduct = false;
        foreach ($quote->getAllItems() as $item) {
            $this->refreshProductPrice($item);
            if ($item->getParentItem()) {
                $itemDetails[$item->getParentItem()->getId()][] = $item;
                continue;
            }
            if ($item->getProduct()->getTypeId() == "configurable") {
                $configItemIds[] = $item->getId();
                $hasConfigProduct = true;
                continue;
            }
            if ($item->getProduct()->getTypeId() == "bundle") {
                $bundleItemIds[] = $item->getId();
                $hasBundleProduct = true;
                continue;
            }
            $this->processItemPrice($item);
        }
        if ($hasBundleProduct) {
            $this->updateBundleItemPrice($bundleItemIds, $itemDetails);
        }
        if ($hasConfigProduct) {
            $this->updateConfigItemPrice($configItemIds, $itemDetails);
        }
    }

    /**
     * Refresh Product Price In Cart
     *
     * @param object $item
     */
    public function refreshProductPrice($item)
    {
        try {
            $item->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * configurable Item price
     *
     * @param array $configItemIds
     * @param array $itemDetails
     * @return void
     */
    public function updateConfigItemPrice($configItemIds, $itemDetails)
    {
        try {
            foreach ($configItemIds as $itemId) {
                foreach ($itemDetails[$itemId] as $item) {
                    $parentItem = $item->getParentItem();
                    $result = $this->processItemPrice($item, $parentItem, true);
                    if ($result['updated']) {
                        $price = $result['price'];
                        $parentItem->setOriginalCustomPrice($price);
                        $parentItem->setRowTotal($item->getQty()*$price);
                        $parentItem->getProduct()->setIsSuperMode(true);
                        $this->saveParentItem($parentItem);
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * save data in model
     *
     * @param object $parentItem
     * @return void
     */
    public function saveParentItem($parentItem)
    {
        $parentItem->save();
    }
    
    /**
     * for bundled products
     *
     * @param array $bundleItemIds
     * @param array $itemDetails
     * @return void
     */
    public function updateBundleItemPrice($bundleItemIds, $itemDetails)
    {
        try {
            foreach ($bundleItemIds as $itemId) {
                $updated = false;
                $totalPrice = 0;
                foreach ($itemDetails[$itemId] as $item) {
                    $parentItem = $item->getParentItem();
                    $result = $this->processItemPrice($item, $parentItem, false, true);
                    if ($result['updated']) {
                        $updated = true;
                    }
                    $totalPrice += $result['price'];
                }
                if ($updated) {
                    $parentItem->setOriginalCustomPrice($totalPrice);
                    $parentItem->setRowTotal($item->getQty()*$totalPrice);
                    $parentItem->getProduct()->setIsSuperMode(true);
                    $this->saveParentItem($parentItem);
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * get original price by item
     *
     * @param array $item
     * @return void
     */
    public function getOriginalPriceByItem($item)
    {
        $price = 0;
        $product = $item->getProduct();
        if ($item->getParentItem()) {
            $price = $item->getParentItem()
                        ->getProduct()
                        ->getPriceModel()
                        ->getChildFinalPrice(
                            $item->getParentItem()->getProduct(),
                            $item->getParentItem()->getQty(),
                            $product,
                            $item->getQty()
                        );
        } elseif (!$item->getParentItem()) {
            $price = $product->getFinalPrice($item->getQty());
        }
        return $price;
    }

    /**
     * Save Quote Item Updates
     *
     * @return string
     */
    public function checkStatus()
    {
        $this->_cart->create()->save();
    }

    /**
     * get Final price
     *
     * @param array $ruleDetails
     * @param double $price
     * @return double
     */
    public function getFinalPrice($ruleDetails, $price)
    {
        if ($ruleDetails['rule_id'] > 0) {
            $amount = $ruleDetails['amount'];
            if (!$ruleDetails['fixed']) {
                $amount = ($price* $amount) / 100;
            }
            if ($ruleDetails['discount']) {
                $price -= $amount;
            } else {
                $price += $amount;
            }
        }
        return $price;
    }

    /**
     * return default price
     *
     * @param array $ruleDetails
     * @param double $price
     * @return void
     */
    public function getDefaultPrice($ruleDetails, $price)
    {
        if ($ruleDetails['rule_id'] > 0) {
            $amount = $ruleDetails['amount'];
            if ($ruleDetails['fixed']) {
                if ($ruleDetails['discount']) {
                    $price = $price + $amount;
                } else {
                    $price = $price - $amount;
                }
            } else {
                if ($ruleDetails['discount']) {
                    $price = ($price * 100)/(100 - $amount);
                } else {
                    $price = ($price * 100)/(100 + $amount);
                }
            }
        }
        return $price;
    }

    /**
     * prints post query
     *
     * @return void
     */
    public function printPostQuery()
    {
        return $this->printPostQuery;
    }

    /**
     * appends sql query
     *
     * @return string
     */
    public function printPreQuery()
    {
        return $this->printPreQuery;
    }

    /**
     * function to check the login status of customer
     *
     * @return boolean
     */
    public function checkLogin()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }

    /**
     * function to get customer id from context
     *
     * @return int customerId
     */
    public function getCustomerId()
    {
        $customerId = 0;
        if ($this->checkLogin()) {
            $customerId = $this->httpContext->getValue('customer_id');
        }
        return $customerId;
    }

    /**
     * get customer id from context
     *
     * @return void
     */
    public function getCustomerGroupId()
    {
        $customerGroup = $this->httpContext->getValue('customer_group');
        return $customerGroup;
    }

    /**
     * check module enabled status
     *
     * @return boolean
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(
            'mppricelist/mppricelist_settings/enable_pricelist',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * validate seller on pricelist when edit
     *
     * @param int $priceListId
     * @return boolean
     */
    public function isRightSeller($priceListId)
    {
        if (!empty($priceListId)) {
            try {
                $priceListCollection = $this->priceList->create()->load($priceListId, 'id');
                if (!empty($priceListCollection)) {
                    if ($this->getCustomerId() == $priceListCollection->getSellerId()) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * check rules when edit
     *
     * @param int $priceRuleId
     * @return boolean
     */
    public function isRightSellerForRule($priceRuleId)
    {
        if (!empty($priceRuleId)) {
            try {
                $priceRuleCollection = $this->priceRule->create()->load($priceRuleId, 'id');
                if (!empty($priceRuleCollection)) {
                    if ($this->getCustomerId() == $priceRuleCollection->getSellerId()) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * get formatted string
     *
     * @param array $getIds
     * @return string
     */
    public function getFormattedString($getIds)
    {
        if (!empty($getIds)) {
            try {
                foreach ($getIds as $id => $valueAtId) {
                    if (empty($id)) {
                        $formattedString = $valueAtId."=true";
                    } else {
                        $formattedString.= '&'.$valueAtId."=true";
                    }
                }
                return $formattedString;
            } catch (\Exception $e) {
                return $formattedString = "";
            }
        }
    }

    /**
     * return status array
     *
     * @return array
     */
    public function getStatusOptions()
    {
        $data = [
            ['value' => '1', 'label' => __('Active')],
            ['value' => '2', 'label' => __('Inactive')]
        ];
        return $data;
    }

    /**
     * validate pricelist id
     *
     * @param int $priceListId
     * @return array
     */
    public function validatePriceListId($priceListId)
    {
        $priceList = [];
        try {
            if (!empty($priceListId)) {
                $priceListData = $this->priceList->create()->getCollection()
                ->addFieldToFilter('id', ['eq'=>$priceListId])
                ->addFieldToFilter('seller_id', ['eq'=>$this->getCustomerId()]);
                if ($priceListData->getSize()) {
                    foreach ($priceListData as $priceList) {
                        return $priceList;
                    }
                }
            }
            return $priceList;
        } catch (\Exception $e) {
            return $priceList;
        }
    }

    /**
     * get rules on pricelist
     *
     * @param int $priceListId
     * @return int
     */
    public function getAssignedRuleOnPriceList($priceListId)
    {
        $assignedRulesId = [];
        if (!empty($priceListId)) {
            try {
                $assignedRulesOnPriceList = $this->assignedRule->create()->getCollection()
                ->addFieldToFilter('pricelist_id', ['eq' => $priceListId]);
                if ($assignedRulesOnPriceList->getSize()) {
                    foreach ($assignedRulesOnPriceList as $assignedRules) {
                        array_push($assignedRulesId, $assignedRules->getRuleId());
                    }
                }
                return $assignedRulesId;
            } catch (\Exception $e) {
                return $assignedRulesId;
            }
        }
    }

    /**
     * get assigned customer on priceList
     *
     * @param int $priceListId
     * @return array
     */
    public function getAssignedCustomerOnPriceList($priceListId)
    {
      
        try {
            $assignedCustomerIdArray = [];
            $userDetails = $this->userDetails->create()->getCollection()
            ->addFieldToFilter('pricelist_id', ['eq'=>$priceListId]);
            if ($userDetails->getSize()) {
                foreach ($userDetails as $userIds) {
                    array_push($assignedCustomerIdArray, $userIds->getUserId());
                }
            }
            return $assignedCustomerIdArray;
        } catch (\Exception $e) {
            return $assignedCustomerIdArray;
        }
    }

    /**
     * show categories selected in category tree
     *
     * @param int $ruleId
     * @return void
     */
    public function showCategoriesSelectedInCategoryTree($ruleId)
    {
        $selectedCategories = [];
        try {
            if (!empty($ruleId)) {
                $ruleItemsCollection =  $this->_items->create()->getCollection()
                ->addFieldToFilter('entity_type', ['eq'=>2])
                ->addFieldToFilter('parent_id', ['eq'=>$ruleId]);
                if (!empty($ruleItemsCollection)) {
                    foreach ($ruleItemsCollection as $rulesItem) {
                        array_push($selectedCategories, $rulesItem->getEntityValue());
                    }
                }
            }
            return $selectedCategories;
        } catch (\Exception $e) {
            return $selectedCategories;
        }
    }

    /**
     * return base currency of the store
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * return currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

     /**
      * get all allowed currency in system config
      *
      * @return string
      */
    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }

    /**
     * @param string $currency
     * @param string $toCurrencies
     * get currency rates
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    /**
     * convert currency
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param double $amount
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

        if ($baseCurrencyCode == $toCurrency) {
            $currencyAmount = $amount/$rates[$fromCurrency];
        } else {
            $amount = $amount/$rates[$fromCurrency];
            $currencyAmount = $this->convertCurrency($amount, $baseCurrencyCode, $toCurrency);
        }
       
        return $currencyAmount;
    }

    /**
     * convert currency
     *
     * @param double $amount
     * @param string $from
     * @param string $to
     */
    public function convertCurrency($amount, $from, $to)
    {
        $finalAmount = $this->_directoryHelper
            ->currencyConvert($amount, $from, $to);
        return $finalAmount;
    }

    /**
     * Clean Cache
     */
    public function clearCache()
    {
        $cacheManager = $this->cacheManager->create();
        $availableTypes = $cacheManager->getAvailableTypes();
        $cacheManager->clean($availableTypes);
    }
}
