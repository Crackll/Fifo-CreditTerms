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

namespace Webkul\MpWholesale\Model\ResourceModel\Product\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\MpWholesale\Model\ResourceModel\Product\Collection as ProductCollection;
use \Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use \Magento\Framework\Event\ManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Data\Collection\EntityFactoryInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends ProductCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface        $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface       $eventManager
     * @param StoreManagerInterface  $storeManager
     * @param mixed                 $mainTable
     * @param mixed                 $eventPrefix
     * @param mixed                 $eventObject
     * @param mixed                 $resourceModel
     * @param string                 $model
     * @param mixed                 $connection
     * @param AbstractDb|null        $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
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

    protected function _renderFiltersBefore()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $authSession = $objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        $userId = $authSession->getUser()->getUserId();
        $role = $authSession->getUser()->getRole();
        $eavAttribute = $objectManager->get(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute::class
        );
        $proAttrId = $eavAttribute->getIdByCode("catalog_product", "name");
        $catalogProductEntityVarchar = $this->getTable('catalog_product_entity_varchar');
        $adminUser = $this->getTable('admin_user');
        $this->getSelect()->join(
            $catalogProductEntityVarchar.' as cpev',
            'main_table.product_id = cpev.entity_id',
            ["product_name" => "value"]
        )->where("cpev.store_id = 0 AND cpev.attribute_id = ".$proAttrId);
        
        $this->getSelect()->join(
            $adminUser.' as cgf',
            'main_table.user_id = cgf.user_id',
            ["username" => "cgf.username",'email' => 'cgf.email']
        );
        $this->addFilterToMap("product_name", "cpev.value");
        if ($role->getRoleName() == 'Wholesaler') {
            $this->getSelect()->where(
                'main_table.user_id ='.$userId
            );
        }
        parent::_renderFiltersBefore();
    }

    protected function _initSelect()
    {
        $this->addFilterToMap('product_name', 'cpev.value');
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('username', 'cgf.username');
        $this->addFilterToMap('email', 'cgf.email');
        parent::_initSelect();
    }
}
