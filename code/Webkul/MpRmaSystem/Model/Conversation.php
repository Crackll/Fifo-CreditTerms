<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Model;

use Webkul\MpRmaSystem\Api\Data\ConversationInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * MpRmaSystem Conversation Model.
 *
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Conversation _getResource()
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Conversation getResource()
 */
class Conversation extends AbstractModel implements ConversationInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpRmaSystem Conversation cache tag.
     */
    const CACHE_TAG = 'mprmasystem_conversation';

    /**
     * @var string
     */
    protected $_cacheTag = 'mprmasystem_conversation';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mprmasystem_conversation';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRmaSystem\Model\ResourceModel\Conversation::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteConversation();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Conversation.
     *
     * @return \Webkul\MpRmaSystem\Model\Conversation
     */
    public function noRouteConversation()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
