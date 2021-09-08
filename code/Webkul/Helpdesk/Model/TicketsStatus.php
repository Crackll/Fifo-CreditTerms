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

use Webkul\Helpdesk\Api\Data\TicketsStatusInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk TicketsStatus Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\TicketsStatus _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\TicketsStatus getResource()
 */
class TicketsStatus extends \Magento\Framework\Model\AbstractModel implements
    TicketsStatusInterface,
    IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk TicketsStatus cache tag
     */
    const CACHE_TAG = 'helpdesk_tickets_status';

    /**#@+
    * Product's Statuses
    */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;
    /**#@-*/

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_tickets_status';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_tickets_status';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\TicketsStatus::class);
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
            return $this->noRouteType();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route TicketsStatus
     *
     * @return \Webkul\Helpdesk\Model\TicketsStatus
     */
    public function noRouteType()
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
     * @return \Webkul\Helpdesk\Api\Data\TicketsStatusInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Prepare product's statuses.
     * Available event marketplace_product_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enable'),
            self::STATUS_DISABLED => __('Disable'),
        ];
    }

    /**
     * toOptionArray Return status option array
     */
    public function toOptionArray()
    {
        $data = [];
        $statusCollection = $this->getCollection()
        ->addFieldToFilter("status", ["eq"=>1]);
        foreach ($statusCollection as $status) {
            $data[] =  ['value'=>$status->getId(), 'label'=>$status->getName()];
        }
        return  $data;
    }
}
