<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model;

use Webkul\Helpdesk\Api\Data\CustomerInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Customer Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\Customer _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\Customer getResource()
 */
class Customer extends \Magento\Framework\Model\AbstractModel implements CustomerInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk Customer cache tag
     */
    const CACHE_TAG = 'helpdesk_customer';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_customer';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_customer';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Customer::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteCustomer();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Customer
     *
     * @return \Webkul\Helpdesk\Model\Customer
     */
    public function noRouteCustomer()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Helpdesk\Api\Data\CustomerInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
