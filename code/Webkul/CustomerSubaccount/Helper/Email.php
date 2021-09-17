<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Helper;

use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Model\Session;
use Magento\Store\Model\ScopeInterface;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Customer Session
     *
     * @var Magento\Customer\Model\Session
     */
    public $customerSession;
    
    /**
     * Translation
     *
     * @var Magento\Framework\Translate\Inline\StateInterface
     */
    public $inlineTranslation;
    
    /**
     * Transport Builder
     *
     * @var Magento\Framework\Mail\Template\TransportBuilder
     */
    public $transportBuilder;
    
    /**
     * Store
     *
     * @var Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    
    /**
     * Customer
     *
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customer;
    
    /**
     * Logger
     *
     * @var \Webkul\CustomerSubaccount\Logger\Logger
     */
    public $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customer
     * @param \Webkul\CustomerSubaccount\Logger\Logger $logger
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customer,
        \Webkul\CustomerSubaccount\Logger\Logger $logger,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        Data $helper,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->subaccountFactory = $subaccountFactory;
    }
    
    /**
     * [generateTemplate description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate(
        $emailTemplateVariables,
        $senderInfo,
        $receiverInfo,
        $emailTempId
    ) {
        $area = \Magento\Framework\App\Area::AREA_FRONTEND;
        try {
            $senderEmail = isset($senderInfo['replyToEmail']) ? $senderInfo['replyToEmail'] : $senderInfo['email'];
            $template =  $this->transportBuilder->setTemplateIdentifier($emailTempId)
                ->setTemplateOptions(
                    [
                            'area' => $area,
                            'store' => $this->storeManager->getStore()->getId(),
                        ]
                )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name'])
            ->setReplyTo($senderEmail, $senderInfo['name']);
            return $this;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Order Notification to Mainaccount
     *
     * @param object $order
     * @return void
     */
    public function sendOrderNotificationToMainaccount($order)
    {
        try {
            if (!$order->getCustomerIsGuest()) {
                $customerId = $order->getCustomerId();
                if ($this->helper->isSubaccountUser($customerId)
                    && $this->helper->getSendOrderNotificationToMainAccount()
                ) {
                    $mainAccountId = $this->helper->getMainAccountId($customerId);
                    $mainUser = $this->helper->getCustomerModelById($mainAccountId);
                    $receiverInfo = [
                        'name' => $mainUser->getName(),
                        'email' => $mainUser->getEmail()
                    ];
                    $senderInfo = [
                        'name' => 'Admin',
                        'email' => $this->scopeConfig->getValue(
                            'customersubaccount/general/manager_email',
                            ScopeInterface::SCOPE_WEBSITE
                        )
                    ];
                    $emailTempVariables = [
                        'subject' => __('Order Placed Notification to Main Account'),
                        'name' => $mainUser->getName(),
                        'orderid' => '#'.$order->getIncrementId(),
                        'subuser' => $order->getCustomerName(),
                    ];
                    $this->inlineTranslation->suspend();
                    $this->generateTemplate(
                        $emailTempVariables,
                        $senderInfo,
                        $receiverInfo,
                        $this->scopeConfig->getValue(
                            'customersubaccount/email/order_notification_to_mainaccount',
                            ScopeInterface::SCOPE_STORE
                        )
                    );
                    $transport = $this->transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Main Account Order Notification
     *
     * @param object $order
     * @return void
     */
    public function sendMainAccountOrderNotification($order)
    {
        try {
            if (!$order->getCustomerIsGuest()) {
                $customerId = $order->getCustomerId();
                if (!$this->helper->isSubaccountUser($customerId)) {
                    $subAccountIds = $this->subaccountFactory->create()
                                                ->getCollection()
                                                ->addFieldToFilter('main_account_id', $customerId)
                                                ->addFieldToFilter('status', 1)
                                                ->getColumnValues('customer_id');
                    $customerIds = $this->helper->getCustomerIds();
                    $subAccountIds = array_intersect($subAccountIds, $customerIds);
                    foreach ($subAccountIds as $subAccountId) {
                        if ($this->helper->willGetOrderNotificationForMainAccount($subAccountId)) {
                            $subAccountUser = $this->helper->getCustomerModelById($subAccountId);
                            $receiverInfo = [
                                'name' => $subAccountUser->getName(),
                                'email' => $subAccountUser->getEmail()
                            ];
                            $senderInfo = [
                                'name' => $order->getCustomerName(),
                                // 'email' => $order->getCustomerEmail()
                                'email' => $this->scopeConfig->getValue(
                                    'customersubaccount/general/manager_email',
                                    ScopeInterface::SCOPE_WEBSITE
                                ),
                                'replyToEmail' => $order->getCustomerEmail()
                            ];
                            $emailTempVariables = [
                                'subject' => __("Main Account's Order Placed Notification"),
                                'name' => $subAccountUser->getName(),
                                'orderid' => '#'.$order->getIncrementId(),
                            ];
                            $this->inlineTranslation->suspend();
                            $this->generateTemplate(
                                $emailTempVariables,
                                $senderInfo,
                                $receiverInfo,
                                $this->scopeConfig->getValue(
                                    'customersubaccount/email/mainaccount_order_notification',
                                    ScopeInterface::SCOPE_STORE
                                )
                            );
                            $transport = $this->transportBuilder->getTransport();
                            $transport->sendMessage();
                            $this->inlineTranslation->resume();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Subaccount Order Notification
     *
     * @param object $order
     * @return void
     */
    public function sendSubAccountOrderNotification($order)
    {
        try {
            if (!$order->getCustomerIsGuest()) {
                $customerId = $order->getCustomerId();
                if ($this->helper->isSubaccountUser($customerId)) {
                    $mainAccountId = $this->helper->getMainAccountId($customerId);
                    $subAccountIds = $this->subaccountFactory->create()
                                                ->getCollection()
                                                ->addFieldToFilter('main_account_id', $mainAccountId)
                                                ->addFieldToFilter('status', 1)
                                                ->getColumnValues('customer_id');
                    $customerIds = $this->helper->getCustomerIds();
                    $subAccountIds = array_intersect($subAccountIds, $customerIds);
                    foreach ($subAccountIds as $subAccountId) {
                        if ($customerId != $subAccountId
                            && $this->helper->willGetOrderNotificationForSubAccount($subAccountId)) {
                            $subAccountUser = $this->helper->getCustomerModelById($subAccountId);
                            $receiverInfo = [
                                'name' => $subAccountUser->getName(),
                                'email' => $subAccountUser->getEmail()
                            ];
                            $mainAccountUser = $this->helper->getCustomerModelById($mainAccountId);
                            $senderInfo = [
                                'name' => $mainAccountUser->getName(),
                                // 'email' => $mainAccountUser->getEmail()
                                'email' => $this->scopeConfig->getValue(
                                    'customersubaccount/general/manager_email',
                                    ScopeInterface::SCOPE_WEBSITE
                                ),
                                'replyToEmail' => $mainAccountUser->getEmail()
                            ];
                            $emailTempVariables = [
                                'subject' => __("Sub Account's Order Placed Notification"),
                                'name' => $subAccountUser->getName(),
                                'orderid' => '#'.$order->getIncrementId(),
                                'subuser' => $order->getCustomerName(),
                            ];
                            $this->inlineTranslation->suspend();
                            $this->generateTemplate(
                                $emailTempVariables,
                                $senderInfo,
                                $receiverInfo,
                                $this->scopeConfig->getValue(
                                    'customersubaccount/email/subaccount_order_notification',
                                    ScopeInterface::SCOPE_STORE
                                )
                            );
                            $transport = $this->transportBuilder->getTransport();
                            $transport->sendMessage();
                            $this->inlineTranslation->resume();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Subaccount Activate Notification from Admin
     *
     * @param object $subAccount
     * @return void
     */
    public function sendSubAccountActivatedNotificationFromAdmin($subAccount)
    {
        try {
            $customerId = $subAccount->getCustomerId();
            $mainAccountId = $subAccount->getMainAccountId();
            $customer = $this->helper->getCustomerModelById($customerId);
            $mainAccount = $this->helper->getCustomerModelById($mainAccountId);

            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail()
            ];
            $senderInfo = [
                'name' => 'Admin',
                'email' => $this->scopeConfig->getValue(
                    'customersubaccount/general/manager_email',
                    ScopeInterface::SCOPE_WEBSITE
                )
            ];
            $emailTempVariables = [
                'subject' => __("Sub Account Activated Notification from Admin"),
                'name' => $customer->getName()
            ];
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $this->scopeConfig->getValue(
                    'customersubaccount/email/subaccount_activated_notification_to_subaccount',
                    ScopeInterface::SCOPE_STORE
                )
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Subaccount Activated Notification from Admin to Mainaccount
     *
     * @param object $subAccount
     * @return void
     */
    public function sendSubAccountActivatedNotificationFromAdminToMainAccount($subAccount)
    {
        try {
            $customerId = $subAccount->getCustomerId();
            $mainAccountId = $subAccount->getMainAccountId();
            $customer = $this->helper->getCustomerModelById($customerId);
            $mainAccount = $this->helper->getCustomerModelById($mainAccountId);

            $receiverInfo = [
                'name' => $mainAccount->getName(),
                'email' => $mainAccount->getEmail()
            ];
            $senderInfo = [
                'name' => 'Admin',
                'email' => $this->scopeConfig->getValue(
                    'customersubaccount/general/manager_email',
                    ScopeInterface::SCOPE_WEBSITE
                )
            ];
            $emailTempVariables = [
                'subject' => __("Sub Account Activated Notification from Admin"),
                'mainname' => $mainAccount->getName(),
                'id' => $subAccount->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail()
            ];
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $this->scopeConfig->getValue(
                    'customersubaccount/email/subaccount_activated_notification_to_mainaccount',
                    ScopeInterface::SCOPE_STORE
                )
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Cart Approved Notification
     *
     * @param object $subaccCart
     * @return void
     */
    public function sendCartApprovedNotification($subaccCart)
    {
        try {
            $customerId = $subaccCart->getCustomerId();
            $customer = $this->helper->getCustomerModelById($customerId);
            $approverId = $this->helper->getCustomerId();
            $approver = $this->helper->getCustomerModelById($approverId);

            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail()
            ];
            $senderInfo = [
                'name' => $approver->getName(),
                // 'email' => $approver->getEmail()
                'email' => $this->scopeConfig->getValue(
                    'customersubaccount/general/manager_email',
                    ScopeInterface::SCOPE_WEBSITE
                ),
                'replyToEmail' => $approver->getEmail()
            ];
            $emailTempVariables = [
                'subject' => __("Cart Approved Notification"),
                'name' => $customer->getName(),
                'cartid' => $subaccCart->getId()
            ];
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $this->scopeConfig->getValue(
                    'customersubaccount/email/cart_approved_notification',
                    ScopeInterface::SCOPE_STORE
                )
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Subaccount Created Notification to Admin
     *
     * @param object $subaccount
     * @return void
     */
    public function sendSubAccountCreatedNotificationToAdmin($subaccount)
    {
        try {
            $customerId = $subaccount->getCustomerId();
            $customer = $this->helper->getCustomerModelById($customerId);
            $creatorId = $this->helper->getCustomerId();
            $creator = $this->helper->getCustomerModelById($creatorId);
            $mainAccountId = $this->helper->getMainAccountId($creatorId);
            $mainAccount = $this->helper->getCustomerModelById($mainAccountId);

            $receiverInfo = [
                'name' => 'Admin',
                'email' => $this->scopeConfig->getValue(
                    'customersubaccount/general/manager_email',
                    ScopeInterface::SCOPE_WEBSITE
                )
            ];
            $senderInfo = [
                'name' => $creator->getName(),
                // 'email' => $creator->getEmail()
                'email' => $this->scopeConfig->getValue(
                    'customersubaccount/general/manager_email',
                    ScopeInterface::SCOPE_WEBSITE
                ),
                'replyToEmail' => $creator->getEmail()
            ];
            $emailTempVariables = [
                'subject' => __("New Sub Account Created Notification"),
                'adminname' => __('Admin'),
                'id' => $customerId,
                'subid' => $subaccount->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'status' => $subaccount->getStatus()?__('Active'):__('Inactive'),
                'mainid' => $mainAccountId,
                'mainname' => $mainAccount->getName(),
            ];
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $this->scopeConfig->getValue(
                    'customersubaccount/email/subaccount_created_notification_to_admin',
                    ScopeInterface::SCOPE_STORE
                )
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Subaccount Created Notification to Mainaccount
     *
     * @param object $subaccount
     * @return void
     */
    public function sendSubAccountCreatedNotificationToMainAccount($subaccount)
    {
        try {
            $customerId = $subaccount->getCustomerId();
            $customer = $this->helper->getCustomerModelById($customerId);
            $creatorId = $this->helper->getCustomerId();
            $creator = $this->helper->getCustomerModelById($creatorId);
            $mainAccountId = $this->helper->getMainAccountId($creatorId);
            $mainAccount = $this->helper->getCustomerModelById($mainAccountId);

            $receiverInfo = [
                'name' => $mainAccount->getName(),
                'email' => $mainAccount->getEmail()
            ];
            $senderInfo = [
                'name' => $creator->getName(),
                // 'email' => $creator->getEmail()
                'email' => $this->scopeConfig->getValue(
                    'customersubaccount/general/manager_email',
                    ScopeInterface::SCOPE_WEBSITE
                ),
                'replyToEmail' => $creator->getEmail()
            ];
            $emailTempVariables = [
                'subject' => __("New Sub Account Created Notification"),
                'subid' => $subaccount->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'status' => $subaccount->getStatus()?__('Active'):__('Inactive'),
                'mainname' => $mainAccount->getName(),
                'creator' => $creator->getName(),
            ];
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $this->scopeConfig->getValue(
                    'customersubaccount/email/subaccount_created_notification_to_mainaccount',
                    ScopeInterface::SCOPE_STORE
                )
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
