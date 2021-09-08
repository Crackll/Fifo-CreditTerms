<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_CustomerSubaccount
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\CustomerSubaccount\Model\ResourceModel;

/**
 * Subaccount Class
 */
class Subaccount extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init("wkcs_subaccounts", "entity_id");
    }
}
