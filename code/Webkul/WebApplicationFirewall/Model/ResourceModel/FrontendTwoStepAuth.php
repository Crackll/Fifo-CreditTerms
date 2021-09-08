<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Model\ResourceModel;

class FrontendTwoStepAuth extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('webkul_frontend_two_step_auth', 'entity_id');
    }
}
