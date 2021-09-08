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

namespace Webkul\MpWalletSystem\Model\ResourceModel\Walletrecord;

use \Webkul\MpWalletSystem\Model\ResourceModel\AbstractCollection;

/**
 * Webkul MpWalletSystem Model Class
 */
class Collection extends AbstractCollection
{
    /**
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpWalletSystem\Model\Walletrecord::class,
            \Webkul\MpWalletSystem\Model\ResourceModel\Walletrecord::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['customer_name'] = 'cgf.name';
    }

    /**
     * Add filter by store
     *
     * @param  int|array|\Magento\Store\Model\Store $store
     * @param  bool                                 $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
