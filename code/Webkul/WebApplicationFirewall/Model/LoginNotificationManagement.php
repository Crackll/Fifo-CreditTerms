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

/**
 * WAF LoginNotificationManagement class
 */
class LoginNotificationManagement implements \Webkul\WebApplicationFirewall\Api\LoginNotificationManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function sendLoginNotification($param)
    {
        return 'hello api GET return the $param ' . $param;
    }
}
