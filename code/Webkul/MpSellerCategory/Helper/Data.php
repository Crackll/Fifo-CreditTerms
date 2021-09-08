<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Helper;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Store\Model\ScopeInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollection;
use Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory as MpSellerCategoryCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;
    const STATUS_ENABLED_LABEL = "Enabled";
    const STATUS_DISABLED_LABEL = "Disabled";

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    
    /**
     * @var \Magento\Framework\App\Cache\ManagerFactory
     */
    protected $_cacheManager;

    /**
     * @var SellerCollection
     */
    protected $_sellerCollection;

    /**
     * @var CustomerCollection
     */
    protected $_customerCollection;

    /**
     * @var \Webkul\MpSellerCategory\Model\CategoryFactory
     */
    protected $_mpSellerCategoryFactory;

    /**
     * @var MpSellerCategoryCollection
     */
    protected $_mpSellerCategoryCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * Excluded Keys While Validation Category Data
     *
     * @var array
     */
    private $excludes = ["product_ids", "form_key"];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Cache\ManagerFactory $cacheManagerFactory
     * @param SellerCollection $sellerCollectionFactory
     * @param CustomerCollection $customerCollectionFactory
     * @param \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory
     * @param MpSellerCategoryCollection $mpSellerCategoryCollectionFactory
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Cache\ManagerFactory $cacheManagerFactory,
        SellerCollection $sellerCollectionFactory,
        CustomerCollection $customerCollectionFactory,
        \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory,
        MpSellerCategoryCollection $mpSellerCategoryCollectionFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        parent::__construct($context);
        $this->_request = $context->getRequest();
        $this->_cacheManager = $cacheManagerFactory;
        $this->_sellerCollection = $sellerCollectionFactory;
        $this->_customerCollection = $customerCollectionFactory;
        $this->_mpSellerCategoryFactory = $mpSellerCategoryFactory;
        $this->_mpSellerCategoryCollectionFactory = $mpSellerCategoryCollectionFactory;
        $this->_mpHelper = $mpHelper;
    }

    /**
     * Get Seller Id
     *
     * @return integer
     */
    public function getSellerId()
    {
        return (int) $this->_mpHelper->getCustomerId();
    }

    /**
     * Check whether seller categories are allowed or not
     *
     * @return boolean
     */
    public function isAllowedSellerCategories()
    {
        return $this->scopeConfig->getValue('mpsellercategory/settings/allow', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check whether seller is allowed to create categories or not
     *
     * @return boolean
     */
    public function isAllowedSellerToManageCategories()
    {
        return $this->scopeConfig->getValue(
            'mpsellercategory/settings/allow_manage_category',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get category filter display name in layered navigation
     *
     * @return string
     */
    public function getCategoryFilterDisplayName()
    {
        return $this->scopeConfig->getValue('mpsellercategory/settings/filter_name', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check whether seller category filter in layered navigation is allowed or not
     *
     * @return boolean
     */
    public function isAllowedSellerCategoryFilter()
    {
        if (!$this->isAllowedSellerCategories()) {
            return false;
        }

        if (in_array($this->getFullActionName(), $this->getAllowedPagesForSellerCategoryFilter())) {
            return true;
        }

        return false;
    }

    /**
     * Join Customer Table with Collection
     *
     * @param object $collection
     *
     * @return MpSellerCategoryCollection
     */
    public function joinCustomer($collection)
    {
        try {
            $collection->joinCustomer();
            if ($this->_mpHelper->getCustomerSharePerWebsite()) {
                $websiteId = $this->_mpHelper->getWebsiteId();
                $collection->addFieldToFilter('website_id', $websiteId);
            }
        } catch (\Exception $e) {
            return $collection;
        }

        return $collection;
    }

    /**
     * Get Allowed Pages For Seller Category Filter
     *
     * @return array
     */
    public function getAllowedPagesForSellerCategoryFilter()
    {
        $pages = [];
        $pages[] = "marketplace_seller_collection";

        return $pages;
    }

    /**
     * Get Seller Category Filter Name
     *
     * @return string
     */
    public function getRequestVar()
    {
        return "seller_category";
    }

    /**
     * Get Full Action Name
     *
     * @return string
     */
    public function getFullActionName()
    {
        return $this->_request->getFullActionName();
    }

    /**
     * Check whether seller filter in active or not
     *
     * @return boolean
     */
    public function isSellerCategoryFilterActive()
    {
        $filter = trim($this->_request->getParam($this->getRequestVar()));
        if ($filter != "") {
            return true;
        }

        return false;
    }

    /**
     * Clean Cache
     */
    public function clearCache()
    {
        $cacheManager = $this->_cacheManager->create();
        $availableTypes = $cacheManager->getAvailableTypes();
        $cacheManager->clean($availableTypes);
    }

    /**
     * Get List of Seller Ids
     *
     * @return array
     */
    public function getSellerIdList()
    {
        $sellerIdList = [];
        $collection = $this->_sellerCollection
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
        $sellerList = ['' => 'Select Seller'];
        $collection = $this->_customerCollection
                            ->create()
                            ->addAttributeToSelect('firstname')
                            ->addAttributeToSelect('lastname')
                            ->addFieldToFilter('entity_id', ['in' => $sellerIdList]);
        foreach ($collection as $item) {
            $sellerList[$item->getId()] = $item->getFirstname().' '.$item->getLastname();
        }
        return $sellerList;
    }

    /**
     * Get Seller Options
     *
     * @return array
     */
    public function getSellerOptions()
    {
        $categoryId = $this->_request->getParam("id");
        $sellerList = [];
        if ($categoryId) {
            $collection = $this->_mpSellerCategoryCollectionFactory->create();
            $collection->joinCustomer($collection);
            $collection->addFieldToFilter('main_table.entity_id', ['eq' => $categoryId]);
            foreach ($collection as $item) {
                $sellerList[] = ['value' => $item->getId(), 'label' => $item->getName()];
            }
        } else {
            $sellerIdList = $this->getSellerIdList();
            $collection = $this->_customerCollection
                                ->create()
                                ->addAttributeToSelect('firstname')
                                ->addAttributeToSelect('lastname');
            $collection = $collection->addFieldToFilter('entity_id', ['in' => $sellerIdList]);
            $sellerList[] = ['value' => '', 'label' => __('Select Seller')];
            foreach ($collection as $item) {
                $sellerList[] = ['value' => $item->getId(), 'label' => $item->getFirstname().' '.$item->getLastname()];
            }
        }

        return $sellerList;
    }

    /**
     * Check whether category exist or not
     *
     * @param string $categoryName
     * @param integer $sellerId
     * @param integer $categoryId
     *
     * @return boolean
     */
    public function isExistingCategory($categoryName, $sellerId = 0, $categoryId = 0)
    {
        if (!$categoryId) {
            $categoryId = $this->_request->getParam("id");
        }

        if (!$sellerId) {
            $sellerId = $this->getSellerId();
        }

        $categoryName = trim($categoryName);
        $collection = $this->_mpSellerCategoryCollectionFactory->create();
        $collection->addFieldToFilter("category_name", ["eq" => $categoryName]);
        $collection->addFieldToFilter("seller_id", ["eq" => $sellerId]);

        if ($categoryId) {
            $collection->addFieldToFilter("entity_id", ["neq" => $categoryId]);
        }

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    /**
     * Get Seller Category
     *
     * @return \Webkul\MpSellerCategory\Model\Category
     */
    public function getSellerCategory()
    {
        $sellerCategory = $this->_mpSellerCategoryFactory->create();
        if (!empty($this->_request->getParam("id"))) {
            $sellerCategory->load($this->_request->getParam("id"));
        }

        return $sellerCategory;
    }

    /**
     * Remove Spaces and Special Characters from Array Values
     *
     * @param array $details
     *
     * @return array
     */
    public function removeSpaces(&$details)
    {
        foreach ($details as $key => $value) {
            if (in_array($key, $this->excludes)) {
                continue;
            }

            $value = trim($value);
            $details[$key] = $this->removeSpecialCharacters($value);
        }
    }

    /**
     * Validate Category Data
     *
     * @param array $details
     * @param boolean $validateSellerId
     *
     * @return array
     */
    public function validateData(&$details, $validateSellerId = false)
    {
        $result = ["error" => false, "msg" => ""];
        $this->removeSpaces($details);
        if (empty($details['category_name'])) {
            $result["error"] = true;
            $result["msg"] = "Category name is required field.";
            return $result;
        }

        if (!isset($details['position'])) {
            $result["error"] = true;
            $result["msg"] = "Position is required field.";
            return $result;
        }

        if (empty($details['status'])) {
            $result["error"] = true;
            $result["msg"] = "Status is required field.";
            return $result;
        }

        if ($validateSellerId && empty($details['seller_id'])) {
            $result["error"] = true;
            $result["msg"] = "Seller is required field.";
            return $result;
        }

        $details['position'] = (int) $details['position'];
        $details['status'] = (int) $details['status'];

        return $result;
    }

    /**
     * Remove Special Characters from String
     *
     * @param string $string
     *
     * @return string
     */
    public function removeSpecialCharacters($string)
    {
        return preg_replace('/[^A-Za-z0-9\-\ ]/', '', $string);
    }

    /**
     * Get All Status
     *
     * @return array
     */
    public function getAllStatus()
    {
        $options = [];
        $options[self::STATUS_ENABLED] = self::STATUS_ENABLED_LABEL;
        $options[self::STATUS_DISABLED] = self::STATUS_DISABLED_LABEL;
        return $options;
    }
}
