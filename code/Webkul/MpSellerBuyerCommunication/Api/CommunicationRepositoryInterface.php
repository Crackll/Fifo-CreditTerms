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
interface CommunicationRepositoryInterface
{
    /**
     * get collection by entity id
     * @param  integer $entityId entity id
     * @return object
     */
    public function getCollectionByEntityId($entityId);

    /**
     * get all queries list by product id
     * @param  int $productId
     * @return object
     */
    public function getAllCollectionByProductId($productId);

    /**
     * get all queries list by product id
     * @param  int $productId
     * @return object
     */
    public function getAllCollectionBySeller($sellerId);
}
