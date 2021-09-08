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
use Magento\Framework\Url\EncoderInterface;

class TwoStepAuthEnabledDisabled extends \Magento\Framework\App\Action\Action
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
     * @var $_urlEncoder
     */
    protected $_urlEncoder;

    /**
     * Constructor
     * @param Magento\Framework\App\Action\Context                           $context
     * @param Magento\Framework\View\Result\PageFactory                      $resultPageFactory
     * @param Magento\Framework\Encryption\EncryptorInterface                $encryptor
     * @param Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param Magento\Customer\Model\SessionFactory                          $customerSession
     * @param Magento\Customer\Api\Customer\RepositoryInterface              $customerRepositoryInterface
     * @param Webkul\WebApplicationFirewall\Helper\OTPHelper                 $otpHelper
     * @param Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory
     * @param Magento\Framework\Stdlib\CookieManagerInterface                $cookieManager
     * @param Magento\Framework\Stdlib\CookieCookieMetadataFactory           $cookieMetadataFactory
     * @param Magento\Framework\Session\SessionManagerInterface              $sessionManager
     * @param Magento\Framework\HTTP\Header                                  $httpHeader
     * @param Magento\Framework\HTTP\PhpEnvironmentRemoteAddress             $remoteIp
     * @param Magento\Framework\Serialize\Serializer\Json                    $jsonHelper
     * @param Magento\Framework\Stdlib\DateTime\DateTime                     $date
     * @param Magento\Framework\App\Response\RedirectInterface               $redirect
     * @param EncoderInterface                                               $urlEncoder
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
        EncoderInterface $urlEncoder
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
        $this->_urlEncoder = $urlEncoder;
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $param = $this->getRequest()->getPostValue();
        if (!empty($param) && isset($param['two_step_auth_register']) && $param['two_step_auth_register']) {
            $session = $this->_customerSession->create();
            $id = (int)$session->getCustomer()->getId();
            $id = $this->_encryptor->encrypt((string)$id);
            $resultRedirect->setPath(
                'waf/account/login',
                [
                    'param' => $this->_urlEncoder->encode($id)
                ]
            );
            $session->logout();
            return $resultRedirect;
        }

        if (!empty($param) && isset($param['two_step_auth'])) {
            $session = $this->_customerSession->create();
            $id = (int)$session->getCustomer()->getId();

            $collection = $this->_frontendTwoStepAuthFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $id]);

            try {
                if ($collection->getSize()) {
                    $collection = $collection->getLastItem();
                    $isAuthEnabled = ($param['two_step_auth'] == 1) ? true : false;
                    if ($isAuthEnabled) {
                        $id = $this->_encryptor->encrypt((string)$id);
                        $enabled = $this->_encryptor->encrypt('1');
                        $resultRedirect->setPath(
                            'waf/account/login',
                            [
                                'param' => $this->_urlEncoder->encode($id),
                                'two_step_auth_enabled' => $this->_urlEncoder->encode($enabled)
                            ]
                        );
                        $session->logout();
                        return $resultRedirect;
                    }

                    $collection->setIsAuthEnabled($isAuthEnabled);
                    $collection->save();
                }
            } catch (\Exception $ex) {
                $this->messageManager->addError(__("Something went wrong"));
            }

            $this->messageManager->addSuccess(__("Saved Successfully"));

            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        }
    }
}
