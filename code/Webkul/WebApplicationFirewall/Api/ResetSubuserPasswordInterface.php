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

interface ResetSubuserPasswordInterface
{

    /**
     * GET for LoginNotification api
     * @param \Magento\User\Api\Data\UserInterface $user
     * @return $this
     */
    public function setUser($user);

    /**
     * Send Password Rest Request
     * @return bool
     * @throws \Exception
     */
    public function sendResetRequest();
}
