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

use Webkul\Helpdesk\Api\Data\ConnectEmailInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk ConnectEmail Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\ConnectEmail _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\ConnectEmail getResource()
 */
class ConnectEmail extends \Magento\Framework\Model\AbstractModel implements ConnectEmailInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk ConnectEmail cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_connect_email';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_connect_email';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_connect_email';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\ConnectEmail::class);
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
            return $this->noRouteConnectEmail();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route ConnectEmail
     *
     * @return \Webkul\Helpdesk\Model\ConnectEmail
     */
    public function noRouteConnectEmail()
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
     * @return \Webkul\Helpdesk\Api\Data\ConnectEmailInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
