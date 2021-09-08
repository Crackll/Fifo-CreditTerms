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

use Webkul\Helpdesk\Api\Data\TicketsCustomAttributesInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk TicketsCustomAttributes Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\TicketsCustomAttributes _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\TicketsCustomAttributes getResource()
 */
class TicketsCustomAttributes extends \Magento\Framework\Model\AbstractModel implements
    TicketsCustomAttributesInterface,
    IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk TicketsCustomAttributes cache tag
     */
    const CACHE_TAG = 'helpdesk_tickets_customattributes';

    /**#@+
    * Product's CustomAttributeses
    */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;
    /**#@-*/

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_tickets_customattributes';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_tickets_customattributes';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\TicketsCustomAttributes::class);
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
     * Load No-Route TicketsCustomAttributes
     *
     * @return \Webkul\Helpdesk\Model\TicketsCustomAttributes
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
     * @return \Webkul\Helpdesk\Api\Data\TicketsCustomAttributesInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Prepare Custom attributes Statuses.
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
     * getAllowedTicketCustomerAttributes Return Allowed attributes
     * @return Array $attributeIds Allowed attributes
     */
    public function getAllowedTicketCustomerAttributes($type)
    {
        $collection = $this->getCollection()
                            ->addFieldToFilter("status", ["eq"=>1])
                            ->addFieldToFilter("field_dependency", $type);
        $attributeIds = [];
        foreach ($collection as $key => $attribute) {
            array_push($attributeIds, $attribute->getAttributeId());
        }
        return $attributeIds;
    }
}
