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
namespace Webkul\WebApplicationFirewall\Model\Validators;

use Webkul\WebApplicationFirewall\Api\Data\VulnerabilityInterface;

/**
 * WAF IpValidator class
 */
class IpValidator extends AbstactValidator implements IpValidatorInterface
{
    const THREAT_CODE = 100;
    const SUCCESS = 0;
    protected $_logData = [];
    /**
     * @inheritDoc
     */
    public function setClientIps($ips)
    {
        $this->setData('client_ips', $ips);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getClientIps()
    {
        return $this->getData('client_ips');
    }

    /**
     * @inheritDoc
     */
    public function setForwardedIps($ips)
    {
        $this->setData('forwarded_ips', $ips);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getForwardedIps()
    {
        return $this->getData('forwarded_ips');
    }

    /**
     * @inheritDoc
     */
    public function setRemoteIps($ips)
    {
        $this->setData('remote_ips', $ips);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRemoteIps()
    {
        return $this->getData('remote_ips');
    }

    /**
     * Validate client ip
     *
     * @return VulnerabilityInterface
     */
    public function validate() : VulnerabilityInterface
    {
        $this->_logData = [];
        $isIpBlackListed = $this->_isBlackListIp();
        $vulnerability = $this->vulnerabilityFactory->create();
        if ($isIpBlackListed) {
            $vulnerability->setScore(self::THREAT_CODE)
                ->setThreatType('IPBlocked')
                ->setMessage(__('This IP is Blocked'));
        }

        $isIpWhiteListed = $this->_isWhiteListIp();
        $whiteListIps = $this->helper->getConfigData('ips_ban', 'white_list');
        if (!$isIpWhiteListed) {
            $vulnerability->setScore(self::THREAT_CODE)
                ->setThreatType('IPNotWhitelisted')
                ->setMessage(__('This IP does not allowed'));
        } elseif ($isIpWhiteListed && $whiteListIps) {
            $vulnerability->setScore(self::SUCCESS)
                ->setThreatType('IPWhitelisted')
                ->setMessage(__('This IP is allowed'));
        }

        $vulnerability->setLogData($this->_logData);

        return $vulnerability;
    }

    /**
     * Validate client country
     *
     * @return VulnerabilityInterface
     */
    public function validateCountry() : VulnerabilityInterface
    {
        $vulnerability = $this->vulnerabilityFactory->create();
        $isCountryBanned = $this->_isCountryBanned();
        if ($isCountryBanned) {
            $vulnerability->setScore(self::THREAT_CODE)
                ->setMessage(__('Request user country blocked by admin'));
        }
        $vulnerability->setLogData($this->_logData)
            ->setThreatType('CountryBanned');

        return $vulnerability;
    }

    /**
     * Check is client ip blacklisted by admin
     *
     * @return bool
     */
    protected function _isCountryBanned() : bool
    {
        $isBanned = false;
        $this->_logData = [];
        $bannedCountries = $this->helper->getConfigData('country_ban', 'specificcountry');

        if ($bannedCountries) {
            $bannedCountries = explode(',', $bannedCountries);
            $requestIps = $this->getClientIps();
            $remoteIps = $this->getRemoteIps();

            if (!empty($remoteIps)) {
                $requestIps = $remoteIps;
            }

            foreach ($requestIps as $clientIp) {
                $response = $this->helper->getGeoIpData($clientIp);
                if (!empty($response) && in_array($response['country_id'], $bannedCountries)) {
                    $isBanned = true;
                    $this->_logData = [
                        'country_blacklisted' => $response['country_id'],
                        'message' => __('%1 country blacklisted')
                    ];
                    break;
                }
            }
        }

        return $isBanned;
    }

    /**
     * Check is client ip blacklisted by admin
     *
     * @return bool
     */
    protected function _isBlackListIp() : bool
    {
        $isBlackList = false;
        $blackListIps = $this->helper->getConfigData('ips_ban', 'black_list');
        if ($blackListIps) {
            $blackListIps = $this->getFormatSavedIps($blackListIps);
            $requestIps = $this->getClientIps();
            $forwardedIps = $this->getForwardedIps();
            $remoteIps = $this->getRemoteIps();
            $requestIps = array_values(array_unique(array_merge($requestIps, $forwardedIps, $remoteIps)));

            foreach ($requestIps as $clientIp) {
                $this->_logData[] = ['message' => __('IP "%1" checked for blacklist', $clientIp)];
                if ($this->helper->checkIp($clientIp, $blackListIps)) {
                    $isBlackList = true;
                    $this->_logData[] = [
                        'ip_blacklisted' => $clientIp,
                        'message' => __('%1 ip blacklisted')
                    ];
                    break;
                }
            }
        } else {
            $this->_logData[] = ['message' => __('blacklisted ips config not found')];
        }

        return $isBlackList;
    }

    /**
     * Check is client ip whitelisted by admin
     *
     * @return bool
     */
    protected function _isWhiteListIp() : bool
    {
        $isWhiteList = false;
        $whiteListIps = $this->helper->getConfigData('ips_ban', 'white_list');
        if ($whiteListIps) {
            $whiteListIps = $this->getFormatSavedIps($whiteListIps);
            $requestIps = $this->getClientIps();
            $forwardedIps = $this->getForwardedIps();
            $remoteIps = $this->getRemoteIps();
            $requestIps = array_values(array_unique(array_merge($requestIps, $forwardedIps, $remoteIps)));

            foreach ($requestIps as $clientIp) {
                $this->_logData[] = ['message' => __('IP "%1" checked for whitelisted', $clientIp)];
                if ($this->helper->checkIp($clientIp, $whiteListIps)) {
                    $isWhiteList = true;
                    $this->_logData = [
                        'ip_whitelisted' => $clientIp,
                        'message' => __('%1 ip whitelisted by admin')
                    ];
                    break;
                }
            }
        } else {
            $isWhiteList = true;
            $this->_logData[] = ['message' => __('whitelisted ips config not found')];
        }
        return $isWhiteList;
    }

    /**
     * Formate saved config value
     *
     * @param string $ips
     * @return array
     */
    private function getFormatSavedIps($ips): array
    {
        return array_filter(array_map('trim', explode(',', $ips)));
    }
}
