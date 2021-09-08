<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model;

use Webkul\Helpdesk\Api\Data\TicketnotesInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Tickets Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\Ticketnotes _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\Ticketnotes getResource()
 */
class Ticketnotes extends \Magento\Framework\Model\AbstractModel implements TicketnotesInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk Tickets cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_notes';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_notes';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_notes';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Ticketnotes::class);
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
            return $this->noRouteTickets();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Tickets
     *
     * @return \Webkul\Helpdesk\Model\Tickets
     */
    public function noRouteTickets()
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
     * @return \Webkul\Helpdesk\Api\Data\TicketnotesInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
