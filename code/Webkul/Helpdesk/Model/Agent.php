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

use Webkul\Helpdesk\Api\Data\AgentInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Agent Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\Agent _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\Agent getResource()
 */
class Agent extends \Magento\Framework\Model\AbstractModel implements AgentInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk Agent cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_agents';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_agents';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_agents';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Agent::class);
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
     * Load No-Route Agent
     *
     * @return \Webkul\Helpdesk\Model\Agent
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
     * @return \Webkul\Helpdesk\Api\Data\AgentInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * toOptionArray Return status option array
     */
    public function toOptionArray()
    {
        $data = [];
        $agentColl = $this->getCollection();
        $joinTable2 = $agentColl->getTable('admin_user');
        
        $agentColl->getSelect()->join(
            $joinTable2.' as au',
            'main_table.user_id = au.user_id',
            [
                'agent_id' => 'au.user_id',
                'firstname' => 'au.firstname',
                'lastname' => 'au.lastname'
            ]
        );
        foreach ($agentColl as $agent) {
            if ($agent->getId()!="") {
                $data[] =  ['value'=>$agent->getAgentId(), 'label'=>$agent->getFirstname()." ".$agent->getLastname()];
            }
        }

        return  $data;
    }
}
