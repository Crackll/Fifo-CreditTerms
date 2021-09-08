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

namespace Webkul\WebApplicationFirewall\Api\Data;

interface SecurityLoginInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const REFERER_URL = 'referer_url';
    const LOGIN_STATUS = 'login_status';
    const ENTITY_ID = 'entity_id';
    const IS_SENT_MAIL = 'is_sent_mail';
    const URL = 'url';
    const PASSWORD = 'password';
    const BROWSER_AGENT = 'browser_agent';
    const IP = 'ip';
    const USERNAME = 'username';
    const TIME = 'time';
    const IS_BRUTE_FORCE = 'is_brute_force';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setId($entityId);

    /**
     * Get username
     * @return string|null
     */
    public function getUsername();

    /**
     * Set username
     * @param string $username
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setUsername($username);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginExtensionInterface $extensionAttributes
    );

    /**
     * Get password
     * @return string|null
     */
    public function getPassword();

    /**
     * Set password
     * @param string $password
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setPassword($password);

    /**
     * Get ip
     * @return string|null
     */
    public function getIp();

    /**
     * Set ip
     * @param string $ip
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setIp($ip);

    /**
     * Get browser_agent
     * @return string|null
     */
    public function getBrowserAgent();

    /**
     * Set browser_agent
     * @param string $browserAgent
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setBrowserAgent($browserAgent);

    /**
     * Get url
     * @return string|null
     */
    public function getUrl();

    /**
     * Set url
     * @param string $url
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setUrl($url);

    /**
     * Get referer_url
     * @return string|null
     */
    public function getRefererUrl();

    /**
     * Set referer_url
     * @param string $refererUrl
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setRefererUrl($refererUrl);

    /**
     * Get login_status
     * @return string|null
     */
    public function getLoginStatus();

    /**
     * Set login_status
     * @param string $loginStatus
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setLoginStatus($loginStatus);

    /**
     * Get is_sent_mail
     * @return string|null
     */
    public function getIsSentMail();

    /**
     * Set is_sent_mail
     * @param string $isSentMail
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setIsSentMail($isSentMail);

    /**
     * Get time
     * @return string|null
     */
    public function getTime();

    /**
     * Set time
     * @param string $time
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setTime($time);

    /**
     * Get is_brute_force
     * @return string|null
     */
    public function getIsBruteForce();

    /**
     * Set is_brute_force
     * @param string $isBruteForce
     * @return \Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface
     */
    public function setIsBruteForce($isBruteForce);
}
