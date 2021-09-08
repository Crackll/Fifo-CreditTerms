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

namespace Webkul\MpWalletSystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Webkul\MpWalletSystem\Model\Wallettransaction;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Webkul MpWalletSystem Controller
 */
class Addpayee extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Mail
     */
    protected $walletHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    protected $walletUpdate;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var $walletPayee
     */
    protected $walletPayee;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * Initialize depndencies
     *
     * @param Context                                            $context
     * @param PageFactory                                        $resultPageFactory
     * @param \Webkul\MpWalletSystem\Helper\Data                 $walletHelper
     * @param WalletUpdateData                                   $walletUpdate
     * @param \Magento\Customer\Model\CustomerFactory            $customerModel
     * @param StoreManagerInterface                              $storeManager
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper
     * @param \Webkul\MpWalletSystem\Model\WalletPayeeFactory    $walletPayee
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\MpWalletSystem\Model\WalletNotification    $walletNotification
     * @param \Magento\Customer\Model\Session                    $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        WalletUpdateData $walletUpdate,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpWalletSystem\Model\WalletPayeeFactory $walletPayee,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\MpWalletSystem\Model\WalletNotification $walletNotification,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->walletUpdate = $walletUpdate;
        $this->customerModel = $customerModel;
        $this->walletNotification = $walletNotification;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
        $this->walletPayee = $walletPayee;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
    
    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $result = [
                'backUrl' => $this->_url->getUrl('customer/account/login')
            ];
            return $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($result)
            );
        }
        $params = $this->getRequest()->getParams();
        $result = $this->validateParams($params);
        if (!$this->getRequest()->isAjax()) {
            return $this->resultRedirectFactory->create()->setPath(
                'mpwalletsystem/transfer/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }

    /**
     * Validate params
     *
     * @param array $params
     * @return bool
     */
    protected function validateParams($params)
    {
        $result = [
            'error' => 0
        ];
        $error = 0;
        if (isset($params) && is_array($params) && !empty($params)
            && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['nickname'])
        ) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'customer_id':
                        if ($value == '') {
                            $error = 1;
                        }
                        break;
                    case 'customer_email':
                        if ($value == '') {
                            $error = 1;
                        }
                        break;
                }
            }
            $this->checkError($error, $result);
            
            $customer = $this->customerModel->create();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $this->setWebsite($websiteId, $customer);
            $customer->loadByEmail($params['customer_email']);
            if ($customer && $customer->getId()) {
                if ($customer->getId() == $params['customer_id']) {
                    $result['error_msg'] = __("You can not add yourself in your payee list.");
                    $result['error'] = 1;
                } elseif ($this->alreadyAddedInPayee($params, $customer)) {
                    $result['error_msg'] = __(
                        "Customer with %1 email address id already present in payee list",
                        $params['customer_email']
                    );
                    $result['error'] = 1;
                } else {
                    $result = $this->addPayeeToCustomer($params, $customer);
                }
            } else {
                $result['error_msg'] = __(
                    "No customer found with email address %1",
                    $params['customer_email']
                );
                $result['error'] = 1;
            }
        } else {
            $result['error'] = 1;
            $result['error_msg'] = __(
                "Data is not validate"
            );
            $this->messageManager->addError(__("Data is not validate"));
        }
        return $result;
    }

    /**
     * Set Website for customer
     *
     * @param int $websiteId
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function setWebsite($websiteId, $customer)
    {
        if (isset($websiteId)) {
            $customer->setWebsiteId($websiteId);
        }
    }

    /**
     * add payee to customer
     *
     * @param array $params
     * @param object $customer
     * @return void
     */
    public function addPayeeToCustomer($params, $customer)
    {
        $payeeModel = $this->walletPayee->create();
        $configStatus = $this->walletHelper->getPayeeStatus();
        if (!$configStatus) {
            $status = $payeeModel::PAYEE_STATUS_ENABLE;
        } else {
            $status = $payeeModel::PAYEE_STATUS_DISABLE;
        }
        $payeeModel->setCustomerId($params['customer_id'])
            ->setNickName($params['nickname'])
            ->setPayeeCustomerId($customer->getEntityId())
            ->setStatus($status)
            ->setWebsiteId($customer->getWebsiteId())
            ->save();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $payeeApprovalRequired = $this->scopeConfig->getValue(
            'mpwalletsystem/transfer_settings/payeestatus',
            $storeScope
        );
        if ($payeeApprovalRequired) {
            $this->setNotificationMessageForAdmin();
        }
        if ($payeeApprovalRequired) {
            $displayCustomMessage = $this->scopeConfig->getValue(
                'mpwalletsystem/transfer_settings/show_payee_message',
                $storeScope
            );
            if ($displayCustomMessage) {
                $message = __(
                    $this->scopeConfig->getValue(
                        'mpwalletsystem/transfer_settings/show_payee_message_content',
                        $storeScope
                    )
                );
            }
        }
        if (!isset($message)) {
            $message = __("Payee is added in your list");
        }
        $this->messageManager->addSuccess($message);
        $result = [
            'error' => 0,
            'success_msg' => __('Payee is added in your list'),
            'backUrl' => $this->_url->getUrl('mpwalletsystem/transfer/index')
        ];
        return $result;
    }

    /**
     * already added in payee
     *
     * @param array $params
     * @param object $customer
     * @return boolean
     */
    public function alreadyAddedInPayee($params, $customer)
    {
        $payeeModel = $this->walletPayee->create()->getCollection()
            ->addFieldToFilter('customer_id', $params['customer_id'])
            ->addFieldToFilter('payee_customer_id', $customer->getEntityId())
            ->addFieldToFilter('website_id', $customer->getWebsiteId());
        if ($payeeModel->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Set notification message for admin
     *
     * @return void
     */
    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->walletNotification->setPayeeCounter(1);
            $this->walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setPayeeCounter($notification->getPayeeCounter()+1);
            }
        }
        $notificationModel->save();
    }

    /**
     * Check Error function
     *
     * @param int $error
     * @param array $result
     */
    public function checkError($error, $result)
    {
        if ($error==1) {
            $result['error'] = 1;
            $result['error_msg'] = __("Please try again later");
        }
    }
}
