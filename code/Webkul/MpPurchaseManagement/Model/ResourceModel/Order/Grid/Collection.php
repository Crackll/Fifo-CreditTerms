<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Model\ResourceModel\Order\Grid;

use Webkul\MpPurchaseManagement\Model\ResourceModel\Order\Collection as PurchaseOrderCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;

class Collection extends PurchaseOrderCollection implements SearchResultInterface
{
    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $wholesaleHelper;

    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @var string
     */
    protected $_eventPrefix;

    /**
     * @var string
     */
    protected $_eventObject;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface     $entityFactoryInterface
     * @param \Psr\Log\LoggerInterface                                      $loggerInterface
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface  $fetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface                     $eventManagerInterface
     * @param \Magento\Store\Model\StoreManagerInterface                    $storeManagerInterface
     * @param \Webkul\MpWholesale\Helper\Data                               $wholesaleHelper
     * @param mixed|null                                                    $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb          $eventPrefix
     * @param mixed                                                         $eventObject
     * @param mixed                                                         $resourceModel
     * @param string                                                        $model
     * @param null                                                          $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null     $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactoryInterface,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $eventManagerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Webkul\MpWholesale\Helper\Data $wholesaleHelper,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactoryInterface,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManagerInterface,
            $storeManagerInterface,
            $connection,
            $resource
        );
        $this->wholesaleHelper = $wholesaleHelper;
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
        return $this->_aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
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
     * join with mpwholesale_userdata to get wholesaler name
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $wholesaleUser = $this->getTable('mpwholesale_userdata');
        $this->getSelect()->join(
            $wholesaleUser.' as cgf',
            'main_table.wholesaler_id = cgf.user_id',
            ["title" => "title"]
        );
        $user = $this->wholesaleHelper->getCurrentUser();
        if ($user->getRole()->getRoleName() == 'Wholesaler') {
            $this->getSelect()->where(
                'main_table.wholesaler_id ='.$user->getUserId().
                " and main_table.status >".\Webkul\MpPurchaseManagement\Model\Order::STATUS_NEW
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * map the title column
     * @return void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap("title", "cgf.title");
        $this->addFilterToMap("status", "main_table.status");
        $this->addFilterToMap("entity_id", "main_table.entity_id");
        parent::_initSelect();
    }
}
