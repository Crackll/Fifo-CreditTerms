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

use Webkul\Helpdesk\Api\Data\BusinesshoursInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Businesshours Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\Businesshours _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\Businesshours getResource()
 */
class Businesshours extends \Magento\Framework\Model\AbstractModel implements BusinesshoursInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk Businesshours cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_businesshours';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_businesshours';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_businesshours';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Businesshours::class);
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
            return $this->noRouteBusinesshours();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Businesshours
     *
     * @return \Webkul\Helpdesk\Model\Businesshours
     */
    public function noRouteBusinesshours()
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
     * @return \Webkul\Helpdesk\Api\Data\BusinesshoursInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * getAgentOffset Return business hours timesatmp offset
     * @return Float $offset business hours timesatmp offset
     */
    public function getBusinesshourOffset($id)
    {
        $offset = 0;
        $businessHours = $this->load($id);
        if ($businessHours->getTimezone() != "") {
            $offset = timezone_offset_get(timezone_open($businessHours->getTimezone()), new \DateTime());
        }
        return $offset;
    }
}
