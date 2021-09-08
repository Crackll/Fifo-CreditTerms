<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Api;

/**
 * @api
 */
interface ConversationRepositoryInterface
{
    /**
     * get collection by entity id
     * @param  integer $entityId entity id
     * @return object
     */
    public function getCollectionByEntityId($entityId);

    /**
     * get collection by query id
     * @param  int $entityId
     * @return object
     */
    public function getCollectionByQueryId($queryId);

    /**
     * get collection by entity id
     * @param  integer $entityId entity id
     * @return object
     */
    public function getCollectionByQueryIds($queryIds = []);

    /**
     * get queryCount
     * @param  object $collection
     * @return int
     */
    public function getQueryCount($collection);

    /**
     * get reply count
     * @param  object $collection
     * @return int
     */
    public function getReplyCount($conv = []);

    /**
     * get seller response collection of by query ids
     * @param  array  $queryIds
     * @return object
     */
    public function getResponseCollectionByQueryIds($queryIds = []);
}
