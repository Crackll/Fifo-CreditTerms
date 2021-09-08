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

use Webkul\Helpdesk\Api\Data\ActivityInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Activity Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\Activity _getResource()
 */
class Activity extends \Magento\Framework\Model\AbstractModel implements ActivityInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk Activity cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_activity';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_activity';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_activity';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Activity::class);
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
     * @return \Webkul\Helpdesk\Api\Data\TicketsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * toOptionArray Returns Activity Options
     * @return Array Activity Options
     */
    public function toOptionArray()
    {
        return $data =  [
                ['value'=>'agent', 'label'=>'Agents'],
                ['value'=>'businesshour', 'label'=>'Business Hours'],
                ['value'=>'customer', 'label'=>'Customers'],
                ['value'=>'ticketcustomfield', 'label'=>'Ticket Custom Fields'],
                ['value'=>'eventsandtrigger', 'label'=>'Events & Triggers'],
                ['value'=>'group', 'label'=>'Groups'],
                ['value'=>'agentlevel', 'label'=>'Agent Level'],
                ['value'=>'organization', 'label'=>'Organizations'],
                ['value'=>'priority', 'label'=>'Ticket Priority'],
                ['value'=>'roles', 'label'=>'Roles'],
                ['value'=>'ticketrule', 'label'=>'Ticket Rules'],
                ['value'=>'ticketstatus', 'label'=>'Ticket Status'],
                ['value'=>'tickettype', 'label'=>'Ticket Types'],
                ['value'=>'ticket', 'label'=>'Tickets'],
                ['value'=>'email', 'label'=>'Emails'],
                ['value'=>'response', 'label'=>'Responses'],
                ['value'=>'supportcenter', 'label'=>'Support Center Informations']
        ];
    }

    /**
     * fieldLabelOptions Returns Activity Options Lebel
     * @return Array Activity Options
     */
    public function fieldLabelOptions()
    {
        return [
            'agent'             =>      'Agent',
            'businesshour'      =>      'Business Hour',
            'customer'          =>      'Customer',
            'ticketcustomfield' =>      'Ticket Custom Field',
            'eventsandtrigger'  =>      'Events & Trigger',
            'group'             =>      'Group',
            'agentlevel'        =>      'Agent Level',
            'organization'      =>      'Organization',
            'priority'          =>      'Ticket Priority',
            'roles'             =>      'Role',
            'ticketrule'        =>      'Ticket Rule',
            'ticketstatus'      =>      'Ticket Status',
            'tickettype'        =>      'Ticket Type',
            'ticket'            =>      'Ticket',
            'email'             =>      'Email Template',
            'response'          =>      'Response',
            'supportcenter'     =>      'Support Center Information'
        ];
    }
}
