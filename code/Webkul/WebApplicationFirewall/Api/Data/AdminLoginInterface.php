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

interface AdminLoginInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const TIMEZONE = 'timezone';
    const COUNTRY_NAME = 'country_name';
    const CITY = 'city';
    const REGION = 'region';
    const EMAIL = 'email';
    const USERNAME = 'username';
    const IP = 'ip';
    const REGION_CODE = 'region_code';
    const LONGITUDE = 'longitude';
    const LOGIN_AT = 'login_at';
    const LATITUDE = 'latitude';
    const ORG = 'org';
    const ENTITY_ID = 'entity_id';
    const IS_TRUSTED = 'is_trusted';
    const BROWSER = 'browser';
    const LOGIN_ATTEMPT = 'login_attempt';
    const REPORTED_UNKNOWN = 'reported_unknown';
    const DEVICE_UUID = 'device_uuid';
    const DEVICE_TYPE = 'device_type';
    const PLATFORM = 'platform';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param string $id
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setId($id);

    /**
     * Get username
     * @return string|null
     */
    public function getUsername();

    /**
     * Set username
     * @param string $username
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setUsername($username);

    /**
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $email
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setEmail($email);

    /**
     * Get ip
     * @return string|null
     */
    public function getIp();

    /**
     * Set ip
     * @param string $ip
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setIp($ip);

    /**
     * Get city
     * @return string|null
     */
    public function getCity();

    /**
     * Set city
     * @param string $city
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setCity($city);

    /**
     * Get region
     * @return string|null
     */
    public function getRegion();

    /**
     * Set region
     * @param string $region
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setRegion($region);

    /**
     * Get region_code
     * @return string|null
     */
    public function getRegionCode();

    /**
     * Set region_code
     * @param string $regionCode
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setRegionCode($regionCode);

    /**
     * Get country_name
     * @return string|null
     */
    public function getCountryName();

    /**
     * Set country_name
     * @param string $countryName
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setCountryName($countryName);

    /**
     * Get latitude
     * @return string|null
     */
    public function getLatitude();

    /**
     * Set latitude
     * @param string $latitude
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setLatitude($latitude);

    /**
     * Get longitude
     * @return string|null
     */
    public function getLongitude();

    /**
     * Set longitude
     * @param string $longitude
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setLongitude($longitude);

    /**
     * Get timezone
     * @return string|null
     */
    public function getTimezone();

    /**
     * Set timezone
     * @param string $timezone
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setTimezone($timezone);

    /**
     * Get org
     * @return string|null
     */
    public function getOrg();

    /**
     * Set org
     * @param string $org
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setOrg($org);

    /**
     * Get login_at
     * @return string|null
     */
    public function getLoginAt();

    /**
     * Set login_at
     * @param string $loginAt
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setLoginAt($loginAt);

    /**
     * Get is_trusted
     * @return string|null
     */
    public function getIsTrusted();

    /**
     * Set is_trusted
     * @param string $isTrusted
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setIsTrusted($isTrusted);

        /**
         * Get browser
         * @return string|null
         */
    public function getBrowser();

    /**
     * Set browser
     * @param string $browser
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setBrowser($browser);

    /**
     * Get login_attempt
     * @return string|null
     */
    public function getLoginAttempt();

    /**
     * Set login_attempt
     * @param string $loginAttempt
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setLoginAttempt($loginAttempt);

    /**
     * Get reported_unknown
     * @return string|null
     */
    public function getReportedUnknown();

    /**
     * Set reported_unknown
     * @param string $reportedUnknown
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setReportedUnknown($reportedUnknown);

    /**
     * Get device_type
     * @return string|null
     */
    public function getDeviceType();

    /**
     * Set device_type
     * @param string $deviceType
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setDeviceType($deviceType);

    /**
     * Get platform
     * @return string|null
     */
    public function getPlatform();

    /**
     * Set platform
     * @param string $platform
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setPlatform($platform);

    /**
     * Get device_uuid
     * @return string|null
     */
    public function getDeviceUuid();

    /**
     * Set device_uuid
     * @param string $deviceUuid
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface
     */
    public function setDeviceUuid($deviceUuid);
}
