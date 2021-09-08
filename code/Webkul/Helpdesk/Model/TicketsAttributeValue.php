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

use Webkul\Helpdesk\Api\Data\TicketsAttributeValueInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk TicketsAttributeValue Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\TicketsAttributeValue _getResource()
 */
class TicketsAttributeValue extends \Magento\Framework\Model\AbstractModel implements
    TicketsAttributeValueInterface,
    IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk TicketsAttributeValue cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_attribute_values';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_attribute_values';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_attribute_values';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\TicketsAttributeValue::class);
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
     * Load No-Route TicketsAttributeValue
     *
     * @return \Webkul\Helpdesk\Model\TicketsAttributeValue
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
     * @return \Webkul\Helpdesk\Api\Data\TicketsAttributeValueInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
