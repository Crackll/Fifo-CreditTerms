<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceProductLabels\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\AbstractModel;

class Label extends AbstractDb
{
    /**
     * @var Null|Object
     */
    protected $_store = null;

    /**
     * {{@inheritDoc}}
     */
    protected function _construct()
    {
        $this->_init('wk_marketplace_product_label', 'id');
    }

    /**
     * Load Function
     *
     * @param AbstractModel $object
     * @param string $value
     * @param string $field
     *
     * @return void
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && ($field === null)) {
            $field = 'id';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Set Store
     *
     * @param Object $store
     *
     * @return Object $store
     */
    public function setStore($store)
    {
        $this->_store = $store;
        
        return $this;
    }

    /**
     * Get Current Store
     *
     * @return Object $store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }
}
