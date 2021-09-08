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

namespace Webkul\MpWholesale\Api\Data;

interface QuoteConversationInterface
{
    const ENTITY_ID                 = 'entity_id';
    const SENDER_ID                 = 'sender_id';
    const RECEIVER_ID               = 'receiver_id';
    const CONVERSATION              = 'conversation';
    const QUOTE_ID                  = 'quote_id';
    const MSG_FROM                  = 'msg_from';
    const CREATED_AT                = 'created_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Sender ID
     *
     * @return int|null
     */
    public function getSenderId();

    /**
     * Get Receiver ID
     *
     * @return int|null
     */
    public function getReceiverId();

    /**
     * Get Conversation
     *
     * @return string|null
     */
    public function getConversation();

    /**
     * Get Quote Id
     *
     * @return int|null
     */
    public function getQuoteId();

    /**
     * Get Message From
     *
     * @return string|null
     */
    public function getMsgFrom();

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setEntityId($id);

    /**
     * Set Sender ID
     *
     * @return int|null
     */
    public function setSenderId($senderId);

    /**
     * Set Receiver ID
     *
     * @return int|null
     */
    public function setReceiverId($receiverId);

    /**
     * Set Conversation
     *
     * @return string|null
     */
    public function setConversation($conversation);

    /**
     * Set Quote Id
     *
     * @return int|null
     */
    public function setQuoteId($quoteId);

    /**
     * Set Message From
     *
     * @return string|null
     */
    public function setMsgFrom($msgFrom);
    
    /**
     * Set Created At
     *
     * @return string|null
     */
    public function setCreatedAt($createdAt);
}
