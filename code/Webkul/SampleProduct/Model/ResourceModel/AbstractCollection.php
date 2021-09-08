<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SampleProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as DbAbstractCollection;
use Magento\Framework\DB\Sql\Expression as SqlExpression;
use Magento\Framework\DB\Select as DBSelect;

/**
 * Abstract collection of webkul SampleProduct model.
 */
abstract class AbstractCollection extends DbAbstractCollection
{
    /**
     * @var array
     */
    public $mappedFields = [];

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $loggerInterface
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManagerInterface
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManagerInterface;
        parent::__construct(
            $entityFactory,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * performAfterLoad method for performing operations after collection load.
     *
     * @param string $tableName
     * @param string $columnName
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        if (count($items)) {
            $connection = $this->getConnection();
        }
    }

    /**
     * Get total count sql.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        return $countSelect;
    }

    /**
     * Create all sample ids retrieving select with limitation
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $sampleIdsSelect = clone $this->getSelect();
        $sampleIdsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $sampleIdsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $sampleIdsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $sampleIdsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $sampleIdsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $sampleIdsSelect->limit($limit, $offset);
        return $sampleIdsSelect;
    }

    /**
     * Retrieve all sample ids for collection
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
     * Add New Field To Collection
     *
     * @param string $field
     * @param string $expression
     */
    public function addFieldToCollection($field, $expression = "")
    {
        if ($expression == "") {
            $this->getSelect()->columns($field);
        } else {
            $this->getSelect()->columns([$field => new SqlExpression($expression)]);
        }
    }

    /**
     * Reset All Columns From Collection
     */
    public function resetColumns()
    {
        $this->getSelect()->reset(DBSelect::COLUMNS);
    }

    /**
     * Add Fields to Collection
     *
     * @param array $fields
     */
    public function addFieldsToCollection($fields)
    {
        $this->mappedFields = $fields;
        foreach ($fields as $field) {
            $this->addFieldToCollection($field);
        }
    }

    /**
     * Get Current Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get Field Query
     *
     * @param string $field
     * @param string $table
     * @param string $alias
     * @param string $condition
     * @param string $groupByField
     *
     * @return string
     */
    public function getFieldQuery($field, $table, $alias = '', $condition = '', $groupByField = '')
    {
        $query = [];
        $query[] = DBSelect::SQL_SELECT;
        $query[] = $field;
        $query[] = DBSelect::FROM;
        $query[] = $table;
        if ($alias != "") {
            $query[] = DBSelect::SQL_AS;
            $query[] = $alias;
        }

        if ($condition != "") {
            $query[] = DBSelect::SQL_WHERE;
            $query[] = '(';
            $query[] = $condition;
            $query[] = ')';
        }

        if ($groupByField != "") {
            $query[] = DBSelect::SQL_GROUP_BY;
            $query[] = $groupByField;
        }

        $query = implode(" ", $query);
        return $query;
    }

    /**
     * Set Condition Column Value in Collection
     *
     * @param string $query
     * @param string $compareValue
     * @param string $defaultValue
     *
     * @return string
     */
    public function setConditionalValue($query, $compareValue = '""', $defaultValue = '""')
    {
        $query = 'IFNULL(NULLIF(('.$query.'), '.$compareValue.'), '.$defaultValue.')';
        return $query;
    }
}
