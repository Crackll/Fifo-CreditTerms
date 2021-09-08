<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */

namespace Webkul\WebApplicationFirewall\Model;

use Magento\Framework\Api\DataObjectHelper;
use Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface;
use Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterfaceFactory;

/**
 * SecurityLogin Model class
 */
class SecurityLogin extends \Magento\Framework\Model\AbstractModel implements SecurityLoginInterface
{
    const LOGIN_FAILED = 0;
    const LOGIN_SUCCESS = 1;

    protected $dataObjectHelper;

    protected $securityloginDataFactory;

    protected $_eventPrefix = 'webkul_webapplicationfirewall_securitylogin';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param SecurityLoginInterfaceFactory $securityloginDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Webkul\WebApplicationFirewall\Model\ResourceModel\SecurityLogin $resource
     * @param \Webkul\WebApplicationFirewall\Model\ResourceModel\SecurityLogin\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        SecurityLoginInterfaceFactory $securityloginDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Webkul\WebApplicationFirewall\Model\ResourceModel\SecurityLogin $resource,
        \Webkul\WebApplicationFirewall\Model\ResourceModel\SecurityLogin\Collection $resourceCollection,
        array $data = []
    ) {
        $this->securityloginDataFactory = $securityloginDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId()
    {
        return $this->_getData(self::ENTITY_ID);
    }

    /**
     * Set securitylogin_id
     * @param string $securityloginId
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setSecurityloginId($securityloginId)
    {
        return $this->setData(self::ENTITY_ID, $securityloginId);
    }

    /**
     * Get username
     * @return string|null
     */
    public function getUsername()
    {
        return $this->_getData(self::USERNAME);
    }

    /**
     * Set username
     * @param string $username
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setUsername($username)
    {
        return $this->setData(self::USERNAME, $username);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getDataExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get password
     * @return string|null
     */
    public function getPassword()
    {
        return $this->_getData(self::PASSWORD);
    }

    /**
     * Set password
     * @param string $password
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setPassword($password)
    {
        return $this->setData(self::PASSWORD, $password);
    }

    /**
     * Get ip
     * @return string|null
     */
    public function getIp()
    {
        return $this->_getData(self::IP);
    }

    /**
     * Set ip
     * @param string $ip
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setIp($ip)
    {
        return $this->setData(self::IP, $ip);
    }

    /**
     * Get browser_agent
     * @return string|null
     */
    public function getBrowserAgent()
    {
        return $this->_getData(self::BROWSER_AGENT);
    }

    /**
     * Set browser_agent
     * @param string $browserAgent
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setBrowserAgent($browserAgent)
    {
        return $this->setData(self::BROWSER_AGENT, $browserAgent);
    }

    /**
     * Get url
     * @return string|null
     */
    public function getUrl()
    {
        return $this->_getData(self::URL);
    }

    /**
     * Set url
     * @param string $url
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * Get referer_url
     * @return string|null
     */
    public function getRefererUrl()
    {
        return $this->_getData(self::REFERER_URL);
    }

    /**
     * Set referer_url
     * @param string $refererUrl
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setRefererUrl($refererUrl)
    {
        return $this->setData(self::REFERER_URL, $refererUrl);
    }

    /**
     * Get login_status
     * @return string|null
     */
    public function getLoginStatus()
    {
        return $this->_getData(self::LOGIN_STATUS);
    }

    /**
     * Set login_status
     * @param string $loginStatus
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setLoginStatus($loginStatus)
    {
        return $this->setData(self::LOGIN_STATUS, $loginStatus);
    }

    /**
     * Get is_sent_mail
     * @return string|null
     */
    public function getIsSentMail()
    {
        return $this->_getData(self::IS_SENT_MAIL);
    }

    /**
     * Set is_sent_mail
     * @param string $isSentMail
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setIsSentMail($isSentMail)
    {
        return $this->setData(self::IS_SENT_MAIL, $isSentMail);
    }

    /**
     * Get time
     * @return string|null
     */
    public function getTime()
    {
        return $this->_getData(self::TIME);
    }

    /**
     * Set time
     * @param string $time
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setTime($time)
    {
        return $this->setData(self::TIME, $time);
    }

    /**
     * Get is_brute_force
     * @return string|null
     */
    public function getIsBruteForce()
    {
        return $this->_get(self::IS_BRUTE_FORCE);
    }

    /**
     * Set is_brute_force
     * @param string $isBruteForce
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setIsBruteForce($isBruteForce)
    {
        return $this->setData(self::IS_BRUTE_FORCE, $isBruteForce);
    }
}
