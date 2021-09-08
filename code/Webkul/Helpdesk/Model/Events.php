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

use Webkul\Helpdesk\Api\Data\EventsInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Events Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\Events _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\Events getResource()
 */
class Events extends \Magento\Framework\Model\AbstractModel implements EventsInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk Events cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_event';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_event';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_event';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Events::class);
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
            return $this->noRouteEvents();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Events
     *
     * @return \Webkul\Helpdesk\Model\Events
     */
    public function noRouteEvents()
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
     * @return \Webkul\Helpdesk\Api\Data\EventsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
