<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Model\ResourceModel;

/**
 * Webkul MpWalletSystem Model Class
 */
class AdminWallet extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('wk_mpwallet_admin_wallet', 'entity_id');
    }

    /**
     * Retrieve store model.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->_store);
    }
}
