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

interface ScanDirectoriesManagementInterface
{

    /**
     * POST for LoginNotification api
     * @return Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesInterface[]
     */
    public function scan();
}
