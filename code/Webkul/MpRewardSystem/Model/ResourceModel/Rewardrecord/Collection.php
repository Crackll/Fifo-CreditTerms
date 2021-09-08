<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Model\ResourceModel\Rewardrecord;

use \Webkul\MpRewardSystem\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpRewardSystem\Model\Rewardrecord::class,
            \Webkul\MpRewardSystem\Model\ResourceModel\Rewardrecord::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
    /**
     * add store filter
     *
     * @param $store
     * @param boolean $withAdmin
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
