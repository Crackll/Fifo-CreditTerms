<?php

/**
 * Webkul_Smtp data helper
 * @category  Webkul
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Smtp\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get Configuration Detail of Smtp
     * @return array of Smtp Configuration Detail
     */

    public function getSmtpConfig()
    {
        $smtpConfig = [
            'host' => $this->scopeConfig->getValue('smtp/general/host'),
            'user_name' => $this->scopeConfig->getValue('smtp/general/user_name'),
            'password' => $this->scopeConfig->getValue('smtp/general/password'),
            'host_url' => $this->scopeConfig->getValue('smtp/general/host_url')
        ];
        $result = false;
        $hostSetting = $this->getHostSettingList($smtpConfig['host']);
        $enable = $this->scopeConfig->getValue('smtp/general/enable');
        if ($hostSetting && $enable) {
            $hostSetting['username'] = $smtpConfig['user_name'];
            $hostSetting['password'] = $smtpConfig['password'];

            $result = ['config' => $hostSetting];
            if ($smtpConfig['host'] !== 'amazon-ses' && $smtpConfig['host'] !== 'custom') {
                $result['hosturl'] = $smtpConfig['host'];
            } else {
                $result['hosturl'] = $smtpConfig['host_url'];
                $port = $this->scopeConfig->getValue('smtp/general/port');
                $result['config']['port'] = isset($hostSetting['port'])? $hostSetting['port'] : $port;
            }
        }
        return $result;
    }

    /**
     * isEnable
     * @return boolean
     */
    public function isEnable()
    {
        return $this->scopeConfig->getValue('smtp/general/enable');
    }

    /**
     * getHostSettingList
     * @param string $host
     * @return false|array
     */

    public function getHostSettingList($host)
    {
        $hostSetting = [
            'zsmtp.hybridzimbra.com' => ['auth' => 'login', 'tsl' => 'tsl', 'port' => '587'],
            'smtp.gmail.com' => ['auth' => 'login', 'ssl'=>'tls', 'port' => '587'],
            'amazon-ses' => ['auth' => 'login', 'ssl' => 'tls', 'port' => '587'],
            'smtp-mail.outlook.com' => ['auth' => 'login', 'ssl' => 'tls', 'port' => '587'],
            'smtp.mail.yahoo.com' => ['auth' => 'login', 'ssl' => 'tls', 'port' => '587'],
            'mail.gmx.com' => ['auth' => 'login', 'ssl' => 'tls', 'port' => '587'],
            'smtp.mail.com' => ['auth' => 'login', 'ssl' => 'tls', 'port' => '587'],
            'smtp.sparkpostmail.com' => ['auth' => 'login', 'ssl' => 'tls', 'port' => '587'],
            'smtp.sendgrid.net' => ['auth' => 'login', 'ssl' => 'tls', 'port' => '587'],
            'smtp.zoho.com' => ['auth' => 'login', 'ssl' => 'tls', 'port' => '587'],
            'custom' => ['auth' => 'login', 'ssl' => 'tls']
        ];
        return isset($hostSetting[$host]) ? $hostSetting[$host] : false;
    }

    /**
     * @return int/null
     */
    public function getStoreId()
    {
        return $this->storeId;
    }
    /**
     * @param int/null $storeId
     */
    public function setStoreId($storeId = null)
    {
        $this->storeId = $storeId;
    }
}
