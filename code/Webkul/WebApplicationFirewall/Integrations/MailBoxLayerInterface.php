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

namespace Webkul\WebApplicationFirewall\Integrations;

interface MailBoxLayerInterface
{
    const CODE = 'mailboxlayer';

    const SERVER_URL = 'http://apilayer.net/api/check';

    /**
     * Validate Email Address
     * @param string $emailAddress
     * @return array|null
     * @throws \Exception
     */
    public function validateEmail($emailAddress);
}
