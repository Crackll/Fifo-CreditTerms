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

namespace Webkul\WebApplicationFirewall\Api;

interface LoginNotificationManagementInterface
{

    /**
     * GET for LoginNotification api
     * @param string $param
     * @return string
     */
    public function sendLoginNotification($param);
}
