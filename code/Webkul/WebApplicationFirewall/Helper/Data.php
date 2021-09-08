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

namespace Webkul\WebApplicationFirewall\Helper;

use Magento\Store\Model\Store;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;

/**
 * WAF module base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH = 'waf_setting/';

    /** @var \Magento\Framework\App\Helper\Context */
    protected $storeManager;

    /** @var DirectoryList */
    protected $directoryList;

    /** @var \Magento\Framework\Locale\Resolver */
    protected $localeResolver;

    /** @var \Magento\Directory\Model\Region */
    protected $regionModel;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Directory\Model\Region $regionModel
     * @param RemoteAddress $remoteAddress
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Directory\Model\Region $regionModel,
        \Webkul\WebApplicationFirewall\Helper\DebugHelper $debugHelper,
        RemoteAddress $remoteAddress,
        JsonHelper $json
    ) {
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->localeResolver = $localeResolver;
        $this->regionModel = $regionModel;
        $this->remoteAddress = $remoteAddress;
        $this->json = $json;
        $this->debugHelper = $debugHelper;
        parent::__construct($context);
    }

    /**
     * Get Config Values
     *
     * @param string $group
     * @param string $field
     * @return string|null
     */
    public function getConfigData($group, $field)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH . $group . DIRECTORY_SEPARATOR . $field
        );
    }

    /**
     * Get is module enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getConfigData('login_security', 'enable');
    }

    /**
     * Notification enabled or not.
     *
     * @return bool
     */
    public function isNotificationEnabled()
    {
        return $this->getConfigData('login_security', 'login_notification');
    }

    /**
     * Check if GeoIp Database available or not
     *
     * @return bool|string
     */
    public function checkIpLocationDb()
    {
        // @codingStandardsIgnoreStart
        $path = $this->directoryList->getPath('media') . '/WAF/GeoIp/GeoIp';
        if (!file_exists($path)) {
            return false;
        }
        $directory = scandir($path, true);
        $filePath = $path . '/' . $directory[0] . '/GeoLite2-City.mmdb';
        if (!file_exists($filePath)) {
            return false;
        }
        // @codingStandardsIgnoreEnd
        return $filePath;
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getBrowserInfo()
    {
        $browserData = $this->getBrowserData();
        $browser = __('%1 on %2', $browserData['name'], $browserData['platform']);
        return $browser;
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getGeoIpData($ipAddress)
    {
        $geoIpData = [];
        try {
            $libPath = $this->checkIpLocationDb();
            if ($libPath && class_exists('GeoIp2\Database\Reader')) {

                $geoIp = new \GeoIp2\Database\Reader($libPath, $this->getLocales());
                $record = $geoIp->city($ipAddress);
                $geoIpData = [
                    'city'       => $record->city->name,
                    'country_id' => $record->country->isoCode,
                    'postcode'   => $record->postal->code
                ];
                if ($record->mostSpecificSubdivision) {
                    $code = $record->mostSpecificSubdivision->isoCode;
                    if ($regionId = $this->regionModel->loadByCode($code, $record->country->isoCode)->getId()) {
                        $geoIpData['region_id'] = $regionId;
                    } else {
                        $geoIpData['region'] = $record->mostSpecificSubdivision->name;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->getLogger()->critical($e);
        }
        return $geoIpData;
    }

    /**
     * Client Browser Info
     *
     * @return array
     */
    public function getBrowserData($userAgent, $all = false)
    {
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $userAgent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $userAgent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }
        if ($all) {
            return [
                'userAgent' => $userAgent,
                'name'      => $ub,
                'platform'  => $platform
            ];
        }
        return $ub;
    }

    /**
     * @return array
     */
    protected function getLocales()
    {
        $language = substr($this->localeResolver->getLocale(), 0, 2) ?: 'en';
        $locales = [$language];
        if ($language !== 'en') {
            $locales[] = 'en';
        }
        return $locales;
    }

    /**
     * Checks given IP against array of masks like 123.123.123.123, 123.123.*.101, 123.123., 123.123.1*.*
     *
     * @param $ip
     * @param $masks
     * @return bool
     */
    public function checkIp($ip, $masks)
    {
        if (in_array($ip, $masks)) {
            return true;
        } else {
            foreach ($masks as $mask) {
                if (substr($mask, -1) == '.' && substr($ip, 0, strlen($mask)) == $mask) {
                    return true; // Case for 123.123. mask
                }
                if (strpos($mask, '*') === false) {
                    continue;
                }
                $maskParts = explode('.', $mask);
                $ipParts = explode('.', $ip);
                foreach ($maskParts as $key => $maskPart) {
                    if ($maskPart == '*') {
                        continue;
                    } elseif (strpos($maskPart, '*') !== false) {
                        // Case like 1*, 1*2, *1
                        $regExp = str_replace('*', '\d{0,3}', $maskPart);
                        if (preg_match('/^' . $regExp . '$/', $ipParts[$key])) {
                            continue;
                        } else {
                            continue 2;
                        }
                    } else {
                        if ($maskPart != $ipParts[$key]) {
                            continue 2;
                        }
                    }
                }
                return true;
            }
            return false;
        }
    }

    /**
     * Decode json data
     *
     * @param string $value
     * @return array
     */
    public function jsonDecode($value)
    {
        return $this->json->unserialize($value);
    }

    /**
     * Encode json data
     *
     * @param array $value
     * @return string
     */
    public function jsonEncode($value)
    {
        return $this->json->serialize($value);
    }

    public function getDebug($type = '')
    {
        return $this->debugHelper;
    }

    public function getLogger($type = '')
    {
        return $this->debugHelper->getLogger($type);
    }
}
