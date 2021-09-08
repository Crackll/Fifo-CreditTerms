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
use Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterfaceFactory;
use Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\User\Model\UserFactory;

/**
 * AdminLogin Model class
 */
class AdminLogin extends \Magento\Framework\Model\AbstractModel implements AdminLoginInterface
{

    protected $dataObjectHelper;

    protected $_eventPrefix = 'webkul_waf_adminlogin';

    protected $adminloginDataFactory;

    protected $_deviceId = null;

    /**
     * constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AdminLoginInterfaceFactory $adminloginDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Webkul\WebApplicationFirewall\Model\ResourceModel\AdminLogin $resource
     * @param \Webkul\WebApplicationFirewall\Model\ResourceModel\AdminLogin\Collection $resourceCollection
     * @param \Webkul\WebApplicationFirewall\Helper\Data $wafHelper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Webkul\WebApplicationFirewall\Model\Notificator $notificator
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     * @param BackendHelper $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        AdminLoginInterfaceFactory $adminloginDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Webkul\WebApplicationFirewall\Model\ResourceModel\AdminLogin $resource,
        \Webkul\WebApplicationFirewall\Model\ResourceModel\AdminLogin\Collection $resourceCollection,
        \Webkul\WebApplicationFirewall\Helper\Data $wafHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\WebApplicationFirewall\Model\Notificator $notificator,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        BackendHelper $backendHelper,
        UserFactory $userFactory,
        array $data = []
    ) {
        $this->adminloginDataFactory = $adminloginDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->wafHelper = $wafHelper;
        $this->authSession = $authSession;
        $this->notificator = $notificator;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->backendHelper = $backendHelper;
        $this->userFactory = $userFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Currenct login admin
     *
     * @return \Magento\User\Model\User
     */
    public function getCurrentUser()
    {
        $userSession = $this->authSession->getUser();
        $user = $this->userFactory->create();
        if ($userSession) {
            $user->load($userSession->getId());
            $this->setUsername($user->getUsername());
            $this->setEmail($user->getEmail());
            $this->setLoginAttempt($this->getLoginAttempt() + 1);
            $this->setName($user->getFirstName().' '.$user->getLastName());
        }
        return $user;
    }

    /**
     * Check notification need to be send or not.
     *
     * @return bool
     */
    public function canSendNotification()
    {
        if (!$this->getId()) {
            return true;
        }

        if ($this->getDeviceUuid() != $this->_deviceId) {
            return true;
        }

        if ($this->getLoginAttempt() < 2) {
            return true;
        }

        if ($this->getIsTrusted()) {
            return false;
        }

        return false;
    }

    /**
     * Set all data from record.
     *
     * @param string $deviceId
     * @return $this
     */
    private function loadFull($deviceId)
    {
        $data = $this->getResource()->loadByDeviceId($deviceId);
        if ($data !== false) {
            $this->setData($data);
        }

        return $this;
    }

    /**
     * Sent partial values to model
     *
     * @param string $deviceId
     * @return $this
     */
    private function loadForUpdate($deviceId)
    {
        $data = $this->getResource()->loadByDeviceId($deviceId);
        if ($data !== false) {
            $this->setId($data['entity_id']);
            $this->setDeviceUuid($data['device_uuid']);
            $this->setIsTrusted($data['is_trusted']);
            $this->setLoginAttempt($this->getLoginAttempt() + 1);
        }

        return $this;
    }

    /**
     * Load user by its username
     *
     * @param string $username
     * @return $this
     */
    public function loadByDeviceId($deviceId, $loadFull = true)
    {
        if ($loadFull) {
            $this->loadFull($deviceId);
        } else {
            $this->loadForUpdate($deviceId);
        }
        return $this;
    }

    /**
     * Process Admin Login Notification
     *
     * @return void
     */
    public function processAdminLoginNotification()
    {
        $dateModel = $this->dateTimeFactory->create();
        if (!$this->wafHelper->isNotificationEnabled()) {
            return true;
        }
        
        $this->_deviceId = $this->getDeviceUuid();
        $this->loadByDeviceId($this->getDeviceUuid(), false);
        $this->getCurrentUser();

        if ($this->getId() && $this->getIsTrusted()) {
            return true;
        }
        $this->setLoginAt($dateModel->gmtDate());
        $this->_sendNotification();
        $this->save();
    }

    /**
     * Send login notification
     *
     * @return void
     */
    protected function _sendNotification()
    {
        try {
            if ($this->canSendNotification()) {
                $newPassResetToken = $this->backendHelper->generateResetPasswordLinkToken();
                $user = $this->getCurrentUser();
                $user->changeResetPasswordLinkToken($newPassResetToken);
                $user->save();
                $this->notificator->sendAdminLogin($this, $user);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

     /**
      * Get id
      * @return string|null
      */
    public function getId()
    {
        return $this->_getData(self::ENTITY_ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
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
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setUsername($username)
    {
        return $this->setData(self::USERNAME, $username);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Webkul\WebApplicationFirewall\Api\Data\AdminLoginExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\WebApplicationFirewall\Api\Data\AdminLoginExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get email
     * @return string|null
     */
    public function getEmail()
    {
        return $this->_getData(self::EMAIL);
    }

    /**
     * Set email
     * @param string $email
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
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
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setIp($ip)
    {
        return $this->setData(self::IP, $ip);
    }

    /**
     * Get city
     * @return string|null
     */
    public function getCity()
    {
        return $this->_getData(self::CITY);
    }

    /**
     * Set city
     * @param string $city
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Get region
     * @return string|null
     */
    public function getRegion()
    {
        return $this->_getData(self::REGION);
    }

    /**
     * Set region
     * @param string $region
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * Get region_code
     * @return string|null
     */
    public function getRegionCode()
    {
        return $this->_getData(self::REGION_CODE);
    }

    /**
     * Set region_code
     * @param string $regionCode
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(self::REGION_CODE, $regionCode);
    }

    /**
     * Get country_name
     * @return string|null
     */
    public function getCountryName()
    {
        return $this->_getData(self::COUNTRY_NAME);
    }

    /**
     * Set country_name
     * @param string $countryName
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setCountryName($countryName)
    {
        return $this->setData(self::COUNTRY_NAME, $countryName);
    }

    /**
     * Get latitude
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->_getData(self::LATITUDE);
    }

    /**
     * Set latitude
     * @param string $latitude
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setLatitude($latitude)
    {
        return $this->setData(self::LATITUDE, $latitude);
    }

    /**
     * Get longitude
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->_getData(self::LONGITUDE);
    }

    /**
     * Set longitude
     * @param string $longitude
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setLongitude($longitude)
    {
        return $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * Get timezone
     * @return string|null
     */
    public function getTimezone()
    {
        return $this->_getData(self::TIMEZONE);
    }

    /**
     * Set timezone
     * @param string $timezone
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setTimezone($timezone)
    {
        return $this->setData(self::TIMEZONE, $timezone);
    }

    /**
     * Get org
     * @return string|null
     */
    public function getOrg()
    {
        return $this->_getData(self::ORG);
    }

    /**
     * Set org
     * @param string $org
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setOrg($org)
    {
        return $this->setData(self::ORG, $org);
    }

    /**
     * Get login_at
     * @return string|null
     */
    public function getLoginAt()
    {
        return $this->_getData(self::LOGIN_AT);
    }

    /**
     * Set login_at
     * @param string $loginAt
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setLoginAt($loginAt)
    {
        return $this->setData(self::LOGIN_AT, $loginAt);
    }

    /**
     * Get is_trusted
     * @return string|null
     */
    public function getIsTrusted()
    {
        return $this->_getData(self::IS_TRUSTED);
    }

    /**
     * Set is_trusted
     * @param string $isTrusted
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setIsTrusted($isTrusted)
    {
        return $this->setData(self::IS_TRUSTED, $isTrusted);
    }

    /**
     * Get browser
     * @return string|null
     */
    public function getBrowser()
    {
        return $this->_getData(self::BROWSER);
    }

    /**
     * Set browser
     * @param string $browser
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setBrowser($browser)
    {
        return $this->setData(self::BROWSER, $browser);
    }

        /**
         * Get login_attempt
         * @return string|null
         */
    public function getLoginAttempt()
    {
        return $this->_getData(self::LOGIN_ATTEMPT);
    }

    /**
     * Set login_attempt
     * @param string $loginAttempt
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setLoginAttempt($loginAttempt)
    {
        return $this->setData(self::LOGIN_ATTEMPT, $loginAttempt);
    }

    /**
     * Get reported_unknown
     * @return string|null
     */
    public function getReportedUnknown()
    {
        return $this->_getData(self::REPORTED_UNKNOWN);
    }

    /**
     * Set reported_unknown
     * @param string $reportedUnknown
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setReportedUnknown($reportedUnknown)
    {
        return $this->setData(self::REPORTED_UNKNOWN, $reportedUnknown);
    }

    /**
     * Get device_type
     * @return string|null
     */
    public function getDeviceType()
    {
        return $this->_getData(self::DEVICE_TYPE);
    }

    /**
     * Set device_type
     * @param string $deviceType
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setDeviceType($deviceType)
    {
        return $this->setData(self::DEVICE_TYPE, $deviceType);
    }

    /**
     * Get platform
     * @return string|null
     */
    public function getPlatform()
    {
        return $this->_getData(self::PLATFORM);
    }

    /**
     * Set platform
     * @param string $platform
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setPlatform($platform)
    {
        return $this->setData(self::PLATFORM, $platform);
    }

    /**
     * Get device_uuid
     * @return string|null
     */
    public function getDeviceUuid()
    {
        return $this->_getData(self::DEVICE_UUID);
    }

    /**
     * Set device_uuid
     * @param string $deviceUuid
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setDeviceUuid($deviceUuid)
    {
        return $this->setData(self::DEVICE_UUID, $deviceUuid);
    }
}
