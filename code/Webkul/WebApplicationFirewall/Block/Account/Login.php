<?php
/**
 * Webkul Software Pvt. Ltd.
 *
 * @category  Webkul
 * @package   Webkul_WebApplicationFirewall
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Block\Account;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Url\DecoderInterface;

class Login extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var FrontendTwoStepAuthFactory
     */
    private $_frontendTwoStepAuthFactory;

    /**
     * @var OTPHelper
     */
    private $_otpHelper;

    /**
     * @var CustomerFactory
     */
    private $_customerFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param DecoderInterface
     */
    protected $_urlDecoder;

    /**
     * Constructor
     * @param \Magento\Framework\View\Element\Template\Context                $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig
     * @param \Magento\Framework\App\RequestInterface                         $request
     * @param \Magento\Framework\Encryption\EncryptorInterface                $encryptor
     * @param \Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory
     * @param \Webkul\WebApplicationFirewall\Helper\OTPHelper                 $otpHelper
     * @param \Magento\Customer\Model\CustomerFactory                         $customerFactory
     * @param DecoderInterface                                                $urlDecoder
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory,
        \Webkul\WebApplicationFirewall\Helper\OTPHelper $otpHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        DecoderInterface $urlDecoder
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->_request = $request;
        $this->_encryptor = $encryptor;
        $this->_frontendTwoStepAuthFactory = $frontendTwoStepAuthFactory;
        $this->_otpHelper = $otpHelper;
        $this->_customerFactory = $customerFactory;
        $this->_urlDecoder = $urlDecoder;
    }

    /**
     * get param
     * @param void
     * @return string|null
     */
    public function getParam()
    {
        return $this->_request->getParam('param');
    }

    /**
     * get Customer Id
     * @param void
     * @return int
     */
    private function getCustomerId()
    {
        return $customerId = (int)$this->_encryptor->decrypt(
            $this->_urlDecoder->decode(
                $this->getParam()
            )
        );
    }

    /**
     * check is new user
     * @param void
     * @return boolean
     */
    public function isNewUser()
    {
        $customerId = $this->getCustomerId();
        if ($customerId) {
            $collection = $this->_frontendTwoStepAuthFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);

            if (!$collection->getSize()) {
                return true;
            }
        }

        return false;
    }

    /**
     * get qrcode
     * @param void
     * @return string
     */
    public function getQrcode()
    {
        $customer = $this->getCustomer();
        return $this->_otpHelper->getQrcode($customer->getEmail());
    }

    /**
     * get customer
     * @param void
     * @return object
     */
    public function getCustomer()
    {
        return $this->_customerFactory->create()->load($this->getCustomerId());
    }

    /**
     * get redirectTo parameter
     * @param void
     * @return string
     */
    public function getRedirectTo()
    {
        return $this->_request->getParam('redirectTo');
    }

    /**
     * get two_step_auth_enabled parameter
     * @param void
     * @return string
     */
    public function getTwoStepAuthEnabled()
    {
        return $this->_request->getParam('two_step_auth_enabled');
    }

    /**
     * Get Config Value
     * @param string
     * @return string|int|float|null
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check trustThisDevice option enabled
     * @param void
     * @return boolean
     */
    public function isTrustThisDeviceOptionEnabled()
    {
        return $this->getConfigValue('waf_setting/two_step_authentication/allow_trusted');
    }
}
