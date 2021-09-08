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

namespace Webkul\WebApplicationFirewall\Controller\Ajax;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Model\SessionFactory;
use Webkul\WebApplicationFirewall\Api\Data\TrustedDeviceCookieInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Url\EncoderInterface;

class Login extends \Magento\Customer\Controller\Ajax\Login
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var FrontendTwoStepAuthFactory
     */
    private $_frontendTwoStepAuthFactory;

    /**
     * @var $_jsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var $_customerSession
     */
    protected $_customerSession;

    /**
     * @var $_twoStepAuthHelper
     */
    protected $_twoStepAuthHelper;

    /**
     * @var $_urlEncoder
     */
    protected $_urlEncoder;

    /**
     * Constructor
     * @param Magento\Framework\App\Action\Context                          $context
     * @param Session                                                         $customerSession
     * @param Magento\Framework\Json\Helper\Data                            $helper
     * @param AccountManagementInterface                                    $customerAccountManagement
     * @param Magento\Framework\Controller\Result\JsonFactory               $resultJsonFactory
     * @param Magento\Framework\Controller\Result\RawFactory                $resultRawFactory
     * @param Magento\Framework\Stdlib\CookieManagerInterface               $cookieManager
     * @param Magento\Framework\Stdlib\Cookie\CookieMetadataFactory         $cookieMetadataFactory
     * @param Magento\Framework\UrlInterface                                $urlInterface
     * @param Magento\Framework\Serialize\SerializerJson                    $jsonHelper
     * @param WebkulWebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory
     * @param WebkulWebApplicationFirewall\Helper\TwoStepAuthHelper         $twoStepAuthHelper
     * @param EncryptorInterface                                            $encryptor
     * @param SessionFactory                                                $sessionFactory
     * @param EncoderInterface                                              $urlEncoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $customerSession,
        \Magento\Framework\Json\Helper\Data $helper,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory,
        \Webkul\WebApplicationFirewall\Helper\TwoStepAuthHelper $twoStepAuthHelper,
        EncryptorInterface $encryptor,
        SessionFactory $sessionFactory,
        EncoderInterface $urlEncoder
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $helper,
            $customerAccountManagement,
            $resultJsonFactory,
            $resultRawFactory,
            $cookieManager,
            $cookieMetadataFactory
        );

        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->urlInterface = $urlInterface;
        $this->_encryptor = $encryptor;
        $this->_customerSession = $sessionFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->_frontendTwoStepAuthFactory = $frontendTwoStepAuthFactory;
        $this->_twoStepAuthHelper = $twoStepAuthHelper;
        $this->_urlEncoder = $urlEncoder;
    }

    /**
     * Login registered users and initiate a session.
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {
            $customer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );

            $id = (int)$customer->getId();
            $email = $customer->getEmail();

            if (!$this->_twoStepAuthHelper->isReallyAuthEnabled($id)) {
                $session = $this->_customerSession->create();
                $session->setCustomerDataAsLoggedIn($customer);
                $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                    $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
                }
                if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                    $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                    $this->getAccountRedirect()->clearRedirectCookie();
                }
            } else {
                if ($this->isLoggedInByTrustedDevice($id, $email)) {
                    $session = $this->_customerSession->create();
                    $session->setCustomerDataAsLoggedIn($customer);
                    $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                    if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                        $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                        $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                        $this->getAccountRedirect()->clearRedirectCookie();
                    }
                } else {
                    $id = $this->_encryptor->encrypt((string)$id);
                    $redirectTo = $this->_redirect->getRedirectUrl();
                    if ($redirectTo) {
                        $url = $this->urlInterface->getUrl(
                            'waf/account/login',
                            [
                                'param' => $this->_urlEncoder->encode($id),
                                'redirectTo' => $this->_urlEncoder->encode($redirectTo)
                            ]
                        );
                    } else {
                        $url = $this->urlInterface->getUrl(
                            'waf/account/login',
                            [
                                'param' => $this->_urlEncoder->encode($id)
                            ]
                        );
                    }

                    $response['redirectUrl'] = $url;
                }
            }
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => __('Invalid login or password.'),
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * is Logged In By Trusted Device
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
            $cookie = $this->cookieManager->getCookie(
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
     * get Encoded Email Id
     * @param string $customerEmailId
     * @return string
     */
    private function getEncodedCookieAcceptableEmailId($customerEmailId)
    {
        return urlencode(hash('sha256', $customerEmailId));
    }
}
