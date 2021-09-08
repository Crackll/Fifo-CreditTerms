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
namespace Webkul\MpSellerBuyerCommunication\Model;

use Webkul\MpSellerBuyerCommunication\Api\Data\ConversationInterface;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\Collection;

class ConversationRepository implements \Webkul\MpSellerBuyerCommunication\Api\ConversationRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\MpSellerBadge\Model\ResourceModel\Badge
     */
    protected $_resourceModel;

    public function __construct(
        ConversationFactory $conversationFactory,
        \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\CollectionFactory $collectionFactory,
        \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation $resourceModel
    ) {

        $this->_resourceModel = $resourceModel;
        $this->_conversationFactory = $conversationFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * get collection by entity id
     * @param  integer $entityId entity id
     * @return object
     */
    public function getCollectionByEntityId($entityId)
    {
        $collection = $this->_conversationFactory->create()->load($entityId);

        return $collection;
    }

    /**
     * get collection by query id
     * @param  int $entityId
     * @return object
     */
    public function getCollectionByQueryId($queryId)
    {
        $collection = $this->_conversationFactory->create()->getCollection()
            ->addFieldToFilter(
                'comm_id',
                [
                    'eq'=>$queryId
                ]
            );
        return $collection;
    }

    /**
     * get collection by entity id
     * @param  integer $entityId entity id
     * @return object
     */
    public function getCollectionByQueryIds($queryIds = [])
    {
        $collection = $this->_conversationFactory->create()->getCollection()
            ->addFieldToFilter(
                'comm_id',
                [
                    'in'=>$queryIds
                ]
            );
        return $collection;
    }

    /**
     * get queryCount
     * @param  object $collection
     * @return int
     */
    public function getQueryCount($collection)
    {
        $count = 0;
        $count = $collection->addFieldToFilter(
            'sender_type',
            [
                'eq'=>'0'
            ]
        )->getSize();
        return $count;
    }

    /**
     * get reply count
     * @param  object $collection
     * @return int
     */
    public function getReplyCount($conv = [])
    {
        $count = 0;
        $collection = $this->getCollectionByQueryIds($conv);
        $count = $collection->addFieldToFilter(
            'sender_type',
            [
                'eq'=>1
            ]
        )->getSize();
        return $count;
    }

    /**
     * get seller response collection of by seller id
     * @param  array  $queryIds
     * @return object
     */
    public function getResponseCollectionByQueryIds($queryIds = [])
    {
        $collection = $this->_conversationFactory->create()->getCollection()
            ->addFieldToFilter(
                'comm_id',
                [
                    'in'=>$queryIds
                ]
            )
            ->addFieldToFilter(
                'sender_type',
                [
                    'eq'=>'1'
                ]
            );
        return $collection;
    }
}
