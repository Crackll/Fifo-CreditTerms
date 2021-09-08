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

interface ValidateHttpRequestInterface
{
    /**
     * Scan HTTP request
     * @return ScanHttpResultInterface
     */
    public function scanHttpRequest();

    /**
     * Scan if requested ip and country is blocked
     * @return ScanHttpResultInterface
     */
    public function scanForBlockedUsers();
}
