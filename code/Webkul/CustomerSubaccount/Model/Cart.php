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


namespace Webkul\CustomerSubaccount\Model;

/**
 * Cart Class
 */
class Cart extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Webkul\CustomerSubaccount\Api\Data\CartInterface
{

    const NOROUTE_ENTITY_ID = 'no-route';

    const CACHE_TAG = 'webkul_customersubaccount_cart';

    protected $_cacheTag = 'webkul_customersubaccount_cart';

    protected $_eventPrefix = 'webkul_customersubaccount_cart';

    /**
     * set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\CustomerSubaccount\Model\ResourceModel\Cart::class);
    }

     /**
      * Load object data.
      *
      * @param int|null $id
      * @param string   $field
      *
      * @return $this
      */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSubaccountCart();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Subaccount.
     */
    public function noRouteSubaccountCart()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\CustomerSubaccount\Api\Data\CartInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
