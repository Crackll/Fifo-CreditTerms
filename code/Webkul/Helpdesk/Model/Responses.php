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

use Webkul\Helpdesk\Api\Data\ResponsesInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Responses Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\Responses _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\Responses getResource()
 */
class Responses extends \Magento\Framework\Model\AbstractModel implements ResponsesInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk Responses cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_responses';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_responses';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_responses';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Responses::class);
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
            return $this->noRouteResponses();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Responses
     *
     * @return \Webkul\Helpdesk\Model\Responses
     */
    public function noRouteResponses()
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
     * @return \Webkul\Helpdesk\Api\Data\ResponsesInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
