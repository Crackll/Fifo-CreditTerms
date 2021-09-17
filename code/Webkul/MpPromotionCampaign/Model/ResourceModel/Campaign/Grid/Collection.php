<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\Collection as CampaignCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;

/**
 * Class Collection
 * Collection for displaying grid
 */
class Collection extends CampaignCollection implements SearchResultInterface
{
    /**
     * Resource initialization
     * @param EntityFactoryInterface   $entityFactory
     * @param LoggerInterface          $logger
     * @param FetchStrategyInterface   $fetchStrategy
     * @param ManagerInterface         $eventManager
     * @param StoreManagerInterface    $storeManagerInterface
     * @param String                   $mainTable
     * @param String                   $eventPrefix
     * @param String                   $eventObject
     * @param String                   $resourceModel
     * @param $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document'
     * @param $connection = null
     * @param AbstractDb              $resource = null
     * @return $this
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManagerInterface,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManagerInterface,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol(
            $this->_getAllIdsSelect($limit, $offset),
            $this->_bindParams
        );
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {

        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Runs before rendering Grid
     *
     * @return void
     */
    public function _renderFiltersBefore()
    {
        $sellerTable = $this->getTable('mppromotionseller_campaign');
        $productTable = $this->getTable('mppromotionseller_product_campaign');
        $campaignJoin = $this->getTable('mppromotionseller_campaign');
     
        $this->getSelect()->where('main_table.status = '.CampaignModel::STATUS_ENABLED)
        ->where('main_table.start_date < main_table.end_date')
        ->columns(['totalsellers' =>
            new \Zend_Db_Expr('(SELECT COUNT(*) FROM '.$sellerTable
                .' WHERE status=0 AND campaign_id=main_table.entity_id)')])
        ->columns(['totalproducts' =>
            new \Zend_Db_Expr('(SELECT COUNT(*) FROM '.$productTable
                .' WHERE  campaign_id=main_table.entity_id)')])
        ->columns(['campaignJoin' =>
                new \Zend_Db_Expr('(SELECT COUNT(*) FROM '.$campaignJoin
                    .' WHERE  campaign_id=main_table.entity_id )')])
        ->columns(['discount' => 'main_table.discount'])
        ->group('main_table.entity_id');
        parent::_renderFiltersBefore();
    }
}
