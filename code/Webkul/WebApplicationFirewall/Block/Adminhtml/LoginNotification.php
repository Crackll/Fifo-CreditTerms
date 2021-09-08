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

namespace Webkul\WebApplicationFirewall\Block\Adminhtml;

/**
 * WAF LoginNotification class
 */
class LoginNotification extends \Magento\Backend\Block\Template
{
    /**
     * Send notification url
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('waf/notification/login', ['_secure' => 1]);
    }
}
