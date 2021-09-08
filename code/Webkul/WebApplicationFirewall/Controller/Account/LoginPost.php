<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Controller\Account;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Webkul\WebApplicationFirewall\Api\Data\TrustedDeviceCookieInterface;
use Magento\Framework\Url\EncoderInterface;

/**
 * Post login customer action.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var FrontendTwoStepAuthFactory
     */
    private $_frontendTwoStepAuthFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
     */
    private $_cookieManager;

    /**
     * @var $_jsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var $_customerSession
     */
    protected $_customerSession;

    /**
     * @var $_urlInterface
     */
    protected $_urlInterface;

    /**
     * @var $_twoStepAuthHelper
     */
    protected $_twoStepAuthHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param EncoderInterface
     */
    protected $_urlEncoder;

    /**
     * Constructor
     * @param Context                                                         $context
     * @param Session                                                         $customerSession
     * @param AccountManagementInterface                                      $customerAccountManagement
     * @param CustomerUrl                                                     $customerHelperData
     * @param Validator                                                       $formKeyValidator
     * @param AccountRedirect                                                 $accountRedirect
     * @param EncryptorInterface                                              $encryptor
     * @param SessionFactory                                                  $sessionFactory
     * @param EncoderInterface                                                $urlEncoder
     * @param Magento\Framework\Serialize\Serializer\Json                     $jsonHelper
     * @param Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory  $frontendTwoStepAuthFactory
     * @param Magento\Framework\Stdlib\CookieManagerInterface                 $cookieManager
     * @param Magento\Framework\UrlInterface                                  $urlInterface
     * @param Webkul\WebApplicationFirewall\Helper\TwoStepAuthHelper          $twoStepAuthHelper
     * @param Magento\Framework\App\Config\ScopeConfigInterface               $scopeConfig
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        EncryptorInterface $encryptor,
        SessionFactory $sessionFactory,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Webkul\WebApplicationFirewall\Helper\TwoStepAuthHelper $twoStepAuthHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->scopeConfig = $scopeConfig;
        $this->_encryptor = $encryptor;
        $this->_customerSession = $sessionFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->_frontendTwoStepAuthFactory = $frontendTwoStepAuthFactory;
        $this->_cookieManager = $cookieManager;
        $this->_urlInterface = $urlInterface;
        $this->_twoStepAuthHelper = $twoStepAuthHelper;
        $this->_urlEncoder = $urlEncoder;

        parent::__construct(
            $context,
            $customerSession,
            $customerAccountManagement,
            $customerHelperData,
            $formKeyValidator,
            $accountRedirect
        );
    }

    /**
     * Login post action
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);

                    if ($this->_twoStepAuthHelper->isReallyAuthEnabled($customer->getId())) {
                        $id = (int)$customer->getId();
                        $email = $customer->getEmail();
                        if ($this->isLoggedInByTrustedDevice($id, $email)) {
                            $this->doLogin($customer);
                        }
                        $id = $this->_encryptor->encrypt((string)$id);
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath('waf/account/login', ['param' => $this->_urlEncoder->encode($id)]);
                        return $resultRedirect;
                    } else {
                        $this->session->setCustomerDataAsLoggedIn($customer);
                        $redirectUrl = $this->accountRedirect->getRedirectCookie();
                        if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                            $this->accountRedirect->clearRedirectCookie();
                            $resultRedirect = $this->resultRedirectFactory->create();
                            $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                            return $resultRedirect;
                        }
                    }
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                } catch (UserLockedException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __($e->getMessage())
                    );
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addError(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }

    /**
     * check loggedin by trusted user
     * @param int $customerId
     * @param string $customerEmail
     * @return boolean
     */
    private function isLoggedInByTrustedDevice($customerId, $customerEmail)
    {
        $flag = false;
        $frontendTwoStepAuthFactory = $this->_frontendTwoStepAuthFactory->create();
        $collection = $frontendTwoStepAuthFactory->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);

        if ($collection->getSize()) {
            $collection = $collection->getLastItem();

            $encodedEmailId = $this->getEncodedCookieAcceptableEmailId($customerEmail);
            $cookie = $this->_cookieManager->getCookie(
                TrustedDeviceCookieInterface::TRUSTED_DEVICE_COOKIE_PREFIX.'_'.$encodedEmailId
            );

            $devicesData = $collection->getDevicesData();
            if (!empty($devicesData) && !empty($cookie)) {
                $devicesData = (array)$this->_jsonHelper->unserialize($devicesData);
                foreach ($devicesData as $deviceData) {
                    if (!empty($deviceData['deviceToken'])) {
                        if ($cookie == $deviceData['deviceToken']) {
                            $flag = true;
                            break;
                        }
                    }
                }
            }
        }

        return $flag;
    }

    /**
     * get Encoded emailId
     * @param string $customerEmailId
     * @return string
     */
    private function getEncodedCookieAcceptableEmailId($customerEmailId)
    {
        return urlencode(hash('sha256', $customerEmailId));
    }

    /**
     * do login programatically
     * @param object $customer
     * @return object
     */
    private function doLogin($customer)
    {
        $session = $this->_customerSession->create();
        $session->setCustomerDataAsLoggedIn($customer);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account');
        return $resultRedirect;
    }
}
