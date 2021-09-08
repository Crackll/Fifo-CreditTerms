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

use Webkul\Helpdesk\Api\Data\SlapolicyInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Tag Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\SupportCenter _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\SupportCenter getResource()
 */
class Slapolicy extends \Magento\Framework\Model\AbstractModel implements SlapolicyInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk SupportCenter cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_sla_policies';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_sla_policies';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_sla_policies';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Slapolicy::class);
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
            return $this->noRouteTag();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route SupportCenter
     *
     * @return \Webkul\Helpdesk\Model\SupportCenter
     */
    public function noRouteTag()
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
     * @return \Webkul\Helpdesk\Api\Data\SlapolicyInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
