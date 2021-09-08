<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomerSubaccount\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer\Mapper as CustomerMapper;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Api\AccountManagementInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Helper class.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Email Notification
     *
     * @var \Magento\Customer\Model\EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * Customer session.
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Store Manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * Http Context
     *
     * @var \Magento\Framework\App\Http\Context
     */
    public $httpContext;

    /**
     * Subaccount Model
     *
     * @var \Webkul\CustomerSubaccount\Model\SubaccountFactory
     */
    public $subaccountFactory;

    /**
     * Group Collection
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    public $_groupCollection;

    /**
     * Customer Repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $_customerRepository;

    /**
     * Customer Mapper
     *
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    public $_customerMapper;

    /**
     * Customer Model
     *
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    public $_customerFactory;

    /**
     * DataObjectHelper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    public $_dataObjectHelper;

    /**
     * Account Manager
     *
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    public $_accountManagement;

    /**
     * Subaccount Cart Model
     *
     * @var \Webkul\CustomerSubaccount\Model\CartFactory
     */
    public $subaccCartFactory;

    /**
     * Checkout Session
     *
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * Customer Model
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerModFactory;

    /**
     * Constructor
     *
     * @param CustomerSession $customerSession
     * @param CustomerMapper $customerMapper
     * @param CustomerInterfaceFactory $customerFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\Helper\Context $context
     * @param HttpContext $httpContext
     * @param GroupCollection $groupCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param CheckoutSession $checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $customerModFactory
     */
    public function __construct(
        CustomerSession $customerSession,
        CustomerMapper $customerMapper,
        CustomerInterfaceFactory $customerFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Helper\Context $context,
        HttpContext $httpContext,
        GroupCollection $groupCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        CheckoutSession $checkoutSession,
        \Magento\Customer\Model\CustomerFactory $customerModFactory
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->subaccountFactory = $subaccountFactory;
        $this->_groupCollection = $groupCollection;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerFactory = $customerFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_accountManagement = $accountManagement;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerModFactory = $customerModFactory;
        parent::__construct($context);
    }

    /**
     * Return Customer id.
     *
     * @return bool|0|1
     */
    public function getCustomerId($customerId = 0)
    {
        if ($customerId) {
            return $customerId;
        }
        $cid = $this->httpContext->getValue('customer_id');
        if (!$cid) {
            $cid = $this->customerSession->getCustomer()->getId();
        }
        return $cid;
    }

    /**
     * Get Subaccount Group Id
     *
     * @return int
     */
    public function getSubaccountGroupId()
    {
        $groupId = 1;
        $coll = $this->_groupCollection
            ->addFieldToFilter('customer_group_code', 'Customer Subaccount');
        foreach ($coll as $key => $value) {
            $groupId = $value->getId();
        }
        return $groupId;
    }

    /**
     * Return Customer id.
     *
     * @return bool|0|1
     */
    public function isSubaccountUser($customerId = 0)
    {
        $customerId = $this->getCustomerId($customerId);
        return $this->subaccountFactory->create()->load($customerId, 'customer_id')->getId();
    }

    public function getSubAccount($customerId = 0)
    {
        $customerId = $this->getCustomerId($customerId);
        return $this->subaccountFactory->create()->load($customerId, 'customer_id');
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Get all Permissions
     *
     * @return array
     */
    public function getAllPermissions()
    {
        $permissions = [
            'cart-approval-required' => __('Cart Approval Required'),
            'can-merge-own-cart-to-main-cart' => __('Can Merge Own Cart to Main Cart'),
            'can-approve-carts' => __('Can Approve Carts'),
            'can-place-order' => __('Can Place Order'),
            'force-usage-main-account-address' => __('Force Usage Main Account Address'),
            'can-view-main-wishlist' => __('Can View Main Wishlist'),
            'can-add-to-main-wishlist' => __('Can Add to Main Wishlist'),
            'can-remove-from-main-wishlist' => __('Can Remove From Main Wishlist'),
            // 'can-add-to-cart-from-main-wishlist' => __('Can Add To Cart From Main Wishlist'),
            'can-view-main-account-order-list' => __('Can View Main Account Order List'),
            'can-view-main-account-order-details' => __('Can View Main Account Order Details'),
            'can-view-sub-account-order-list' => __('Can View Sub Account Order List'),
            'can-view-sub-account-order-details' => __('Can View Sub Account Order Details'),
            'will-get-notified-on-order-place-by-main-account' => __('Will Get Notified on Order Place by Main Account'),
            'will-get-notified-on-order-place-by-sub-account' => __('Will Get Notified on Order Place by Sub Account'),
            'can-create-sub-accounts' => __('Can Create Sub Accounts'),
            'can-edit-sub-accounts' => __('Can Edit Sub Accounts'),
            'can-delete-sub-accounts' => __('Can Delete Sub Accounts'),
            'can-login-to-sub-accounts' => __('Can Login to Sub Accounts'),
            'can-review-products' => __('Can Review Products'),
        ];
        return $permissions;
    }

    /**
     * Get Allowed Permission by Admin
     *
     * @return array
     */
    public function getManageablePermissions()
    {
        $list = $this->scopeConfig->getValue(
            'customersubaccount/general/manageable_permissions',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        return explode(',', $list);
    }

    /**
     * Get Subaccount Approval Required
     *
     * @return 0|1
     */
    public function getSubaccountApprovalRequired()
    {
        return $this->scopeConfig->getValue(
            'customersubaccount/general/approval_required',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Is Show in Recent Orders?
     *
     * @return 0|1
     */
    public function isShowInRecentOrders()
    {
        return $this->scopeConfig->getValue(
            'customersubaccount/general/show_in_recent_orders',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Send Order Notification to Main Account
     *
     * @return 0|1
     */
    public function getSendOrderNotificationToMainAccount()
    {
        return $this->scopeConfig->getValue(
            'customersubaccount/general/send_order_notification',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Allow Forbidden Access Management
     *
     * @return 0|1
     */
    public function getAllowForbiddenAccessManagement()
    {
        return $this->scopeConfig->getValue(
            'customersubaccount/general/allow_forbidden_access',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Customer By Id.
     * @param int $customerId
     *
     * @return collection object
     */
    public function getCustomerById($customerId)
    {
        $customer = $this->_customerRepository->getById($customerId);
        return $customer;
    }

    /**
     * Get Customer Model By Id.
     * @param int $customerId
     *
     * @return collection object
     */
    public function getCustomerModelById($customerId)
    {
        $customer = $this->customerModFactory->create()->load($customerId);
        return $customer;
    }

    /**
     * If User Have Permission
     *
     * @param string $permission
     * @param integer $customerId
     * @return boolean
     */
    public function checkIfUserHavePermission($permission, $customerId = 0)
    {
        $status = true;
        $customerId = $this->getCustomerId($customerId);
        if ($this->isSubaccountUser($customerId)) {
            $status = false;
            if (in_array($permission, $this->getManageablePermissions())) {
                $permissions = $this->subaccountFactory->create()->load($customerId, 'customer_id')
                                                                ->getAvailablePermissions();
                if (in_array($permission, explode(',', $permissions))) {
                    $status = true;
                }
            }
        }
        return $status;
    }

    /**
     * Is Cart Approval Required?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function isCartApprovalRequired($customerId = 0)
    {
        if ($this->isSubaccountUser($customerId)) {
            return $this->checkIfUserHavePermission('cart-approval-required', $customerId);
        } else {
            return false;
        }
    }

    /**
     * Can Merge Cart to Main Cart?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canMergeCartToMainCart($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-merge-own-cart-to-main-cart', $customerId);
    }

    /**
     * Can Approve Carts?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canApproveCarts($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-approve-carts', $customerId);
    }

    /**
     * Can Place Order?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canPlaceOrder($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-place-order', $customerId);
    }

    /**
     * Is Forced Main Address?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function isForcedMainAddress($customerId = 0)
    {
        if ($this->isSubaccountUser($customerId)) {
            return $this->checkIfUserHavePermission('force-usage-main-account-address', $customerId);
        } else {
            return false;
        }
    }

    /**
     * Can View Main Wishlist?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canViewMainWishlist($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-view-main-wishlist', $customerId);
    }

    /**
     * Can Add to Main Wishlist?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canAddToMainWishlist($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-add-to-main-wishlist', $customerId);
    }

    /**
     * Can Remove from Main Wishlist?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canRemoveFromMainWishlist($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-remove-from-main-wishlist', $customerId);
    }

    // public function canAddToCartFromMainWishlist($customerId = 0)
    // {
    //     return $this->checkIfUserHavePermission('can-add-to-cart-from-main-wishlist', $customerId);
    // }

    /**
     * Can View Main Account Order List?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canViewMainAccountOrderList($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-view-main-account-order-list', $customerId);
    }

    /**
     * Can View Main Account Order Details?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canViewMainAccountOrderDetails($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-view-main-account-order-details', $customerId);
    }

    /**
     * Can View Subaccount Order List?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canViewSubAccountOrderList($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-view-sub-account-order-list', $customerId);
    }

    /**
     * Can View Subaccount Order Details?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canViewSubAccountOrderDetails($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-view-sub-account-order-details', $customerId);
    }

    /**
     * Will Get Order Notification for Main Account?
     *
     * @param integer $customerId
     * @return void
     */
    public function willGetOrderNotificationForMainAccount($customerId = 0)
    {
        return $this->checkIfUserHavePermission('will-get-notified-on-order-place-by-main-account', $customerId);
    }

    /**
     * Will Get Order Notification for Subaccount?
     *
     * @param integer $customerId
     * @return void
     */
    public function willGetOrderNotificationForSubAccount($customerId = 0)
    {
        return $this->checkIfUserHavePermission('will-get-notified-on-order-place-by-sub-account', $customerId);
    }

    /**
     * Can Create Subaccounts?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canCreateSubAccounts($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-create-sub-accounts', $customerId);
    }

    /**
     * Can Edit Subaccounts?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canEditSubAccounts($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-edit-sub-accounts', $customerId);
    }

    /**
     * Can Delete Subaccounts?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canDeleteSubAccounts($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-delete-sub-accounts', $customerId);
    }

    /**
     * Can Login to Subaccounts?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canLoginToSubAccounts($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-login-to-sub-accounts', $customerId);
    }

    /**
     * Can Review Products?
     *
     * @param integer $customerId
     * @return boolean
     */
    public function canReviewProducts($customerId = 0)
    {
        return $this->checkIfUserHavePermission('can-review-products', $customerId);
    }

    /**
     * Save Customer
     *
     * @param array $customerData
     * @param integer $customerId
     * @param integer $websiteId
     * @return array
     */
    public function saveCustomerData($customerData, $customerId = 0, $websiteId = 0)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        }
        if (!empty($customerData)) {
            try {
                $customerData['website_id'] = $websiteId;
                $customerData['group_id'] = $this->getSubaccountGroupId();
                $customerData['disable_auto_group_change'] = 0;
                $customerData['middlename'] = '';
                $customerData['default_billing'] = '';
                $customerData['default_shipping'] = '';
                $customerData['confirmation'] = '';
                $customerData['sendemail_store_id'] = 1;

                if ($customerId) {
                    $currentCustomer = $this->_customerRepository->getById($customerId);
                    $customerData = array_merge(
                        $this->_customerMapper->toFlatArray($currentCustomer),
                        $customerData
                    );
                    $customerData['id'] = $customerId;
                }
                /** @var CustomerInterface $customer */
                $customer = $this->_customerFactory->create();
                $this->_dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                // Save customer
                if ($customerId) {
                    $this->_customerRepository->save($customer);

                    $this->getEmailNotification()->credentialsChanged(
                        $customer,
                        $currentCustomer->getEmail()
                    );
                } else {
                    $customer = $this->_accountManagement->createAccount($customer);
                    $customerId = $customer->getId();
                }
            } catch (\Exception $e) {
                return ['error'=>1, 'message'=>$e->getMessage()];
            }
        }
        return ['error'=>0, 'customer_id'=>$customerId];
    }

    /**
     * Get Email Notification
     *
     * @return object
     */
    public function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Get Mainaccount Id
     *
     * @param int $customerId
     * @return int
     */
    public function getMainAccountId($customerId = 0)
    {
        $customerId = $this->getCustomerId($customerId);
        $collection = $this->subaccountFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('customer_id', $customerId);
        if ($collection->getSize()) {
            $customerId = $collection->getLastItem()->getMainAccountId();
        }
        return $customerId;
    }

    /**
     * Check if Customer can Edit Subaccount
     *
     * @param int $customerId
     * @param int $subaccountId
     * @return int
     */
    public function checkIfCustomerCanEditSubaccount($customerId, $subaccountId)
    {
        $customerId = $this->getMainAccountId($customerId);
        $collectionRes = $this->subaccountFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('customer_id', $subaccountId)
                                ->addFieldToFilter('main_account_id', $customerId);
        return $collectionRes->getSize();
    }

    /**
     * Get Quote
     *
     * @return object
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
    
    /**
     * Is Cart Approved?
     *
     * @param integer $quoteId
     * @return integer
     */
    public function isCartApproved($quoteId = 0)
    {
        if (!$quoteId) {
            $quoteId = $this->getQuote()->getId();
        }
        $result = $this->subaccCartFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('quote_id', $quoteId)
                            ->addFieldToFilter('status', 1)
                            ->getSize();
        return $result;
    }

    /**
     * Get Customer IDs
     *
     * @return array
     */
    public function getCustomerIds()
    {
        $result = $this->customerModFactory->create()
                                    ->getCollection()
                                    ->getColumnValues('entity_id');
        return $result;
    }
}
