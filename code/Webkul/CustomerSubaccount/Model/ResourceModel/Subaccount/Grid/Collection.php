<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_CustomerSubaccount
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Model\ResourceModel\Subaccount\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\CustomerSubaccount\Model\ResourceModel\Subaccount\Collection as SubaccountCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Collection
 * Collection for displaying grid
 */
class Collection extends SubaccountCollection implements SearchResultInterface
{
    /**
     * Resource initialization
     * @param EntityFactoryInterface   $entityFactory,
     * @param LoggerInterface          $logger,
     * @param FetchStrategyInterface   $fetchStrategy,
     * @param ManagerInterface         $eventManager,
     * @param String                   $mainTable,
     * @param String                   $eventPrefix,
     * @param String                   $eventObject,
     * @param String                   $resourceModel,
     * @param $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
     * @param $connection = null,
     * @param AbstractDb              $resource = null
     * @return $this
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
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
     * Init Select
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('customer_name', 'cgf.name');
        $this->addFilterToMap('parent_account_name', 'cgf1.name');
        $this->addFilterToMap('main_account_name', 'cgf2.name');
        parent::_initSelect();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $joinTable = $this->getTable('customer_grid_flat');
        $this->getSelect()->join(
            $joinTable.' as cgf',
            'main_table.customer_id = cgf.entity_id',
            [
                'customer_name'=>'name'
            ]
        )->join(
            $joinTable.' as cgf1',
            'main_table.parent_account_id = cgf1.entity_id',
            [
                'parent_account_name'=>'name'
            ]
        )->join(
            $joinTable.' as cgf2',
            'main_table.main_account_id = cgf2.entity_id',
            [
                'main_account_name'=>'name'
            ]
        );
        parent::_renderFiltersBefore();
    }
}
