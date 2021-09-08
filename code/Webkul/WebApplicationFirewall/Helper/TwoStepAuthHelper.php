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

namespace Webkul\WebApplicationFirewall\Helper;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Url\EncoderInterface;

/**
 * WAF TwoStepAuthHelper
 */
class TwoStepAuthHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory
     */
    private $frontendTwoStepAuthFactory;

    /**
     * @param \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @param \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $urlInterface;

    /**
     * Constructor
     * @param Magento\Framework\App\Helper\Context                          $context
     * @param Magento\Framework\App\Config\ScopeConfigInterface             $scopeConfig
     * @param WebkulWebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory
     * @param Magento\Framework\Encryption\EncryptorInterface               $encryptor
     * @param Magento\Framework\UrlInterface                                $urlInterface
     * @param SessionFactory                                                $sessionFactory
     * @param EncoderInterface                                              $urlEncoder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\UrlInterface $urlInterface,
        SessionFactory $sessionFactory,
        EncoderInterface $urlEncoder
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->sessionFactory = $sessionFactory;
        $this->frontendTwoStepAuthFactory = $frontendTwoStepAuthFactory;
        $this->encryptor = $encryptor;
        $this->urlInterface = $urlInterface;
        $this->urlEncoder = $urlEncoder;
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
     * check is module enable
     * @param void
     * @return boolean [description]
     */
    public function isModuleEnable()
    {
        return $this->getConfigValue('waf_setting/two_step_authentication/enable');
    }

    /**
     * get customer id
     * @param void
     * @return [type] [description]
     */
    public function getCustomerId()
    {
        return (int)$this->sessionFactory->create()->getCustomer()->getId();
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
            $collection = $this->frontendTwoStepAuthFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);

            if (!$collection->getSize()) {
                return true;
            }
        }

        return false;
    }

    /**
     * check is auth enabled
     * @param void
     * @return boolean
     */
    public function getIsAuthEnabled()
    {
        $customerId = $this->getCustomerId();
        if ($customerId) {
            $collection = $this->frontendTwoStepAuthFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);

            if ($collection->getSize()) {
                $collection = $collection->getLastItem();
                return $collection->getIsAuthEnabled();
            }
        }

        return false;
    }

    /**
     * check is auth enabled at admin and customer both end
     * @param int $customerId
     * @return boolean
     */
    public function isReallyAuthEnabled($customerId)
    {
        $customerId = (int)$customerId;
        if ($customerId && $this->isModuleEnable()) {
            $collection = $this->frontendTwoStepAuthFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);

            if ($collection->getSize()) {
                $collection = $collection->getLastItem();
                return $collection->getIsAuthEnabled();
            }
        }

        return false;
    }

    /**
     * get authentication form url
     * @param void
     * @return string
     */
    public function getAuthenticationFormUrl()
    {
        $id = $this->encryptor->encrypt((string)$this->getCustomerId());
        return $this->urlInterface->getUrl('waf/account/login', ['param' => $this->urlEncoder->encode($id)]);
    }
}
