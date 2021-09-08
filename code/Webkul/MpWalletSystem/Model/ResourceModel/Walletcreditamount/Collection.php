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

namespace Webkul\MpWalletSystem\Model\ResourceModel\Walletcreditamount;

use \Webkul\MpWalletSystem\Model\ResourceModel\AbstractCollection;

/**
 * Webkul MpWalletSystem Model Class
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * Init collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpWalletSystem\Model\Walletcreditamount::class,
            \Webkul\MpWalletSystem\Model\ResourceModel\Walletcreditamount::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * Filter collection by specified store ids
     *
     * @param array|int[] $store
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
