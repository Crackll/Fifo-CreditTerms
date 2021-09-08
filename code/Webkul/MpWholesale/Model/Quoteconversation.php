<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Model;

use Webkul\MpWholesale\Api\Data\QuoteConversationInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Quoteconversation extends AbstractModel implements QuoteConversationInterface, IdentityInterface
{
    const CACHE_TAG = 'wholesaler_quotesconversation';
    /**
     * @var string
     */
    protected $_cacheTag = 'wholesaler_quotesconversation';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wholesaler_quotesconversation';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWholesale\Model\ResourceModel\Quoteconversation::class);
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getSenderId()
    {
        return $this->getData(self::SENDER_ID);
    }

    public function setSenderId($senderId)
    {
        return $this->setData(self::SENDER_ID, $senderId);
    }

    public function getReceiverId()
    {
        return $this->getData(self::RECEIVER_ID);
    }

    public function setReceiverId($receiverId)
    {
        return $this->setData(self::RECEIVER_ID, $receiverId);
    }

    public function getConversation()
    {
        return $this->getData(self::CONVERSATION);
    }

    public function setConversation($conversation)
    {
        return $this->setData(self::CONVERSATION, $conversation);
    }

    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    public function getMsgFrom()
    {
        return $this->getData(self::MSG_FROM);
    }

    public function setMsgFrom($msgFrom)
    {
        return $this->setData(self::MSG_FROM, $msgFrom);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
