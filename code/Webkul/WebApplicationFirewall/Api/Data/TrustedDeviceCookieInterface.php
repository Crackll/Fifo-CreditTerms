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

interface TrustedDeviceCookieInterface
{
    /**
     * cookie key prefix for trusted device
     */
    const TRUSTED_DEVICE_COOKIE_PREFIX = 'webkul_trusted_device_cookie';
}
