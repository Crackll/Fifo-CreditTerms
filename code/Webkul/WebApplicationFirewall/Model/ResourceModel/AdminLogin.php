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

namespace Webkul\WebApplicationFirewall\Model\ResourceModel;

/**
 * WAF AdminLogin class
 */
class AdminLogin extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('webkul_adminlogin_attempt', 'entity_id');
    }

    /**
     * Load data by specified username
     *
     * @param string $username
     * @return array
     */
    public function loadByDeviceId($deviceId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable())->where('device_uuid=:device_uuid');

        $binds = ['device_uuid' => $deviceId];

        return $connection->fetchRow($select, $binds);
    }
}
