<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Model\ResourceModel\WalletPayee\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\MpWalletSystem\Model\ResourceModel\WalletPayee\Collection as WalletPayeeCollection;
use \Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use \Magento\Framework\Event\ManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Data\Collection\EntityFactoryInterface;
use \Webkul\MpWalletSystem\Logger\Logger;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Webkul MpWalletSystem Model Class
 */
class Collection extends WalletPayeeCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * Initialize dependencies
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface        $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface       $eventManager
     * @param StoreManagerInterface  $storeManager
     * @param mixed                  $mainTable
     * @param mixed                  $eventPrefix
     * @param mixed                  $eventObject
     * @param mixed                  $resourceModel
     * @param string                 $model
     * @param mixed                  $connection
     * @param AbstractDb|null        $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        Logger $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
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
     * Initialize dependencies
     *
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
     * Backward compatibility with EAV collection.
     *
     * @param int $limit
     * @param int $offset
     *
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
     * Init Select funciton
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $customerGridFlat = $this->getTable('customer_grid_flat');
        $customerGridFlat1 = $this->getTable('customer_grid_flat');
        $this->getSelect()->join(
            $customerGridFlat.' as cgf',
            'main_table.customer_id = cgf.entity_id',
            ['customer_name' => 'name']
        );
        $this->getSelect()->join(
            $customerGridFlat1.' as cgf1',
            'main_table.payee_customer_id = cgf1.entity_id',
            ['payee_customer_name' => 'name','payee_customer_email' => 'email']
        );
        $this->addFilterToMap('customer_name', 'cgf.name');
        $this->addFilterToMap('payee_customer_name', 'cgf1.name');
        $this->addFilterToMap('payee_customer_email', 'cgf1.email');

        parent::_initSelect();
    }

    /**
     * Apply custom filtering
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->group("main_table.entity_id");
        parent::_renderFiltersBefore();
    }
}
