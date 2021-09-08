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

use Webkul\WebApplicationFirewall\Api\Data\TrustedDeviceCookieInterface;
use Magento\Framework\Url\DecoderInterface;

class TwoStepAuthenticate extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var FrontendTwoStepAuthFactory
     */
    private $_frontendTwoStepAuthFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var OTPHelper
     */
    private $_otpHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
     */
    private $_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory CookieMetadataFactory
     */
    private $_cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $_sessionManager;

    /**
     * @var $_httpHeader
     */
    protected $_httpHeader;

    /**
     * @var $_remoteIp
     */
    protected $_remoteIp;

    /**
     * @var $_jsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var $_date
     */
    protected $_date;

    /**
     * @var $_date
     */
    protected $_redirect;

    /**
     * @var $_urlDecoder
     */
    protected $_urlDecoder;

    /**
     * Constructor
     * @param Magento\Framework\App\Action\Context                           $context
     * @param Magento\Framework\View\Result\PageFactory                      $resultPageFactory
     * @param Magento\Framework\Encryption\EncryptorInterface                $encryptor
     * @param Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param Magento\Customer\Model\SessionFactory                          $customerSession
     * @param Magento\Customer\Api\CustomerRepositoryInterface               $customerRepositoryInterface
     * @param Webkul\WebApplicationFirewall\Helper\OTPHelper                 $otpHelper
     * @param Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory
     * @param Magento\Framework\Stdlib\CookieManagerInterface                $cookieManager
     * @param Magento\Framework\Stdlib\Cookie\CookieMetadataFactory          $cookieMetadataFactory
     * @param Magento\Framework\Session\SessionManagerInterface              $sessionManager
     * @param Magento\Framework\HTTP\Header                                  $httpHeader
     * @param Magento\Framework\HTTP\PhpEnvironment\RemoteAddress            $remoteIp
     * @param Magento\Framework\Serialize\Serializer\Json                    $jsonHelper
     * @param Magento\Framework\Stdlib\DateTime\DateTime                     $date
     * @param Magento\Framework\App\Response\RedirectInterface               $redirect
     * @param DecoderInterface                                               $urlDecoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Webkul\WebApplicationFirewall\Helper\OTPHelper $otpHelper,
        \Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteIp,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        DecoderInterface $urlDecoder
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_encryptor = $encryptor;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_otpHelper = $otpHelper;
        $this->_frontendTwoStepAuthFactory = $frontendTwoStepAuthFactory;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->_httpHeader = $httpHeader;
        $this->_remoteIp = $remoteIp;
        $this->_jsonHelper = $jsonHelper;
        $this->_date = $date;
        $this->_redirect = $redirect;
        $this->_urlDecoder = $urlDecoder;
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $param = $this->getRequest()->getPostValue();
        if (!empty($param)) {
            $totp = null;
            $resultRedirect = $this->resultRedirectFactory->create();
            $id = (int)$this->_encryptor->decrypt($this->_urlDecoder->decode($param['param']));
            $customer = $this->_customerRepositoryInterface->getById($id);
            $secretKey = $this->getSecretKeyByCustomerId($id);

            if ($secretKey) {
                $totp = $this->_otpHelper->getTOTPBySecretKey($secretKey, $customer->getEmail());
            } else {
                $totp = $this->_otpHelper->getTOTP($customer->getEmail());
            }

            if ($totp->verify($param['otp'])) {
                $session = $this->_customerSession->create();
                $session->setCustomerDataAsLoggedIn($customer);

                $this->registerTwoStepAuthentication(
                    $id,
                    $session->getTOTPSecretKey(),
                    $customer->getEmail()
                );

                if (isset($param['is_trusted_device']) && $param['is_trusted_device'] == 'on') {
                    $this->registerAsTrustedDevice($id);
                }

                if (isset($param['two_step_auth_enabled']) && !empty($param['two_step_auth_enabled'])) {
                    $register = (boolean)$this->_urlDecoder->decode($param['two_step_auth_enabled']);
                    if ($register) {
                        $this->doAuthEnabled($id);
                    }
                }

                $path = 'customer/account';

                if (isset($param['redirectTo']) && !empty($param['redirectTo'])) {
                    $path = $this->_urlDecoder->decode($param['redirectTo']);
                } else {
                    $this->messageManager->addSuccess(__("OTP Valid"));
                }

                $resultRedirect->setPath($path);
                return $resultRedirect;
            } else {
                $this->messageManager->addError(__("Not Valid"));
                $resultRedirect->setPath(
                    'waf/account/login',
                    [
                        'param' => $param['param'],
                        'redirectTo' => $param['redirectTo'],
                        'two_step_auth_enabled' => $param['two_step_auth_enabled']
                    ]
                );
                return $resultRedirect;
            }
        }
    }

    /**
     * get secret key by customer id
     * @param int $customerId
     * @return string
     */
    private function getSecretKeyByCustomerId($customerId)
    {
        $customerAuthData = $this->getTwoStepAuthDataByCustomerId($customerId);
        if ($customerAuthData) {
            return $this->_encryptor->decrypt($customerAuthData->getSecretKey());
        }

        return false;
    }

    /**
     * register two step authentication
     * @param int $customerId
     * @param string $secretKey
     * @param string $email
     * @return void
     */
    private function registerTwoStepAuthentication($customerId, $secretKey, $email)
    {
        $customerAuthData = $this->getTwoStepAuthDataByCustomerId($customerId);
        if (!$customerAuthData) {
            $customerAuthData = $this->_frontendTwoStepAuthFactory->create();
            $customerAuthData->setCustomerId($customerId);
            $customerAuthData->setSecretKey($this->_encryptor->encrypt($secretKey));
            $customerAuthData->setCustomerEmail($email);
            $customerAuthData->save();
        }
    }

    /**
     * register as trusted device
     * @param int $customerId
     * @return void
     */
    private function registerAsTrustedDevice($customerId)
    {
        $customerAuthData = $this->getTwoStepAuthDataByCustomerId($customerId);
        if ($customerAuthData) {
            $deviceInfo = $this->getDeviceInfo();
            $deviceToken = $this->generateTokenForDevice();

            $this->doRegisterAsTrustedDevice(
                $customerAuthData->getId(),
                $deviceInfo,
                $deviceToken
            );

            $this->saveCookieOnClient($deviceToken, $customerId);
        }
    }

    /**
     * get two step authentication data by customer Id
     * @param int $customerId
     * @return boolean|object
     */
    private function getTwoStepAuthDataByCustomerId($customerId)
    {
        $frontendTwoStepAuthFactory = $this->_frontendTwoStepAuthFactory->create();
        $collection = $frontendTwoStepAuthFactory->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);

        if ($collection->getSize()) {
            return $collection->getLastItem();
        }

        return false;
    }

    /**
     * do Register As Trusted Device
     * @param int $id
     * @param string $deviceInfo
     * @param string $deviceToken
     * @return void
     */
    private function doRegisterAsTrustedDevice($id, $deviceInfo, $deviceToken)
    {
        $frontendTwoStepAuthFactory = $this->_frontendTwoStepAuthFactory->create()->load($id);
        $devicesData = $frontendTwoStepAuthFactory->getDevicesData();

        $data = [
            'deviceToken' => $deviceToken,
            'deviceInfo' => $deviceInfo,
            'deviceRegisteredAt' => $this->_date->gmtDate()
        ];

        if (!empty($devicesData)) {
            $devicesData = (array)$this->_jsonHelper->unserialize($devicesData);
            $devicesData[] = $data;
        } else {
            $devicesData = [];
            $devicesData[] = $data;
        }

        $frontendTwoStepAuthFactory->setDevicesData(
            $this->_jsonHelper->serialize($devicesData)
        );
        $frontendTwoStepAuthFactory->save();
    }

    /**
     * save Cookie On Client
     * @param string $deviceToken [description]
     * @param int $customerId
     * @return void
     */
    private function saveCookieOnClient($deviceToken, $customerId)
    {
        $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setHttpOnly(true)
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());

        $customer = $this->_customerRepositoryInterface->getById($customerId);
        $encodedEmailId = $this->getEncodedCookieAcceptableEmailId($customer->getEmail());

        $this->_cookieManager->setPublicCookie(
            TrustedDeviceCookieInterface::TRUSTED_DEVICE_COOKIE_PREFIX.'_'.$encodedEmailId,
            $deviceToken,
            $publicCookieMetadata
        );
    }

    /**
     * get Encoded Cookie Acceptable Email Id
     * @param string $customerEmailId
     * @return string
     */
    private function getEncodedCookieAcceptableEmailId($customerEmailId)
    {
        return urlencode(hash('sha256', $customerEmailId));
    }

    /**
     * get Device Info
     * @param void
     * @return string
     */
    private function getDeviceInfo()
    {
        $userAgent = $this->_httpHeader->getHttpUserAgent();
        $remoteIp = $this->_remoteIp->getRemoteAddress();
        $data = [
            'userAgent' => $userAgent,
            'remoteIp' => $remoteIp
        ];

        return $this->_jsonHelper->serialize($data);
    }

    /**
     * generate Token For Device
     * @param void
     * @return string
     */
    private function generateTokenForDevice()
    {
        $hash = $this->_encryptor->encrypt(uniqid(time()));
        $doubleHash = $this->_encryptor->encrypt(uniqid($hash));
        return $hash.$doubleHash;
    }

    /**
     * do Authentication Enable By Customer
     * @param int $customerId
     * @return void
     */
    private function doAuthEnabled($customerId)
    {
        $customerAuthData = $this->getTwoStepAuthDataByCustomerId($customerId);
        if ($customerAuthData) {
              $customerAuthData = $this->_frontendTwoStepAuthFactory->create()
                  ->load($customerAuthData->getEntityId());
              $customerAuthData->setIsAuthEnabled(true);
              $customerAuthData->save();
        }
    }
}
