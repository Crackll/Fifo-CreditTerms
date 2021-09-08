<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
    }

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpSellerCategory\Model\Category::class,
            \Webkul\MpSellerCategory\Model\ResourceModel\Category::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * Add filter by store.
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool                                 $withAdmin
     *
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }

        return $this;
    }

    /**
     * Remove records
     */
    public function removeCategories()
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = $item->getId();
        }

        if (empty($ids)) {
            return $this;
        }

        $quoted = $this->getConnection()->quoteInto('IN (?)', $ids);
        $this->getConnection()->delete($this->getMainTable(), "entity_id {$quoted}");
        return $this;
    }

    /**
     * Enable Categories
     */
    public function enableCategories()
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = $item->getId();
        }

        if (empty($ids)) {
            return $this;
        }

        $bind = ['status' => 1];
        $quoted = $this->getConnection()->quoteInto('IN (?)', $ids);
        $where = ["entity_id {$quoted}"];
        $this->getConnection()->update($this->getMainTable(), $bind, $where);
        return $this;
    }

    /**
     * Disable Categories
     */
    public function disableCategories()
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = $item->getId();
        }

        if (empty($ids)) {
            return $this;
        }

        $bind = ['status' => 2];
        $quoted = $this->getConnection()->quoteInto('IN (?)', $ids);
        $where = ["entity_id {$quoted}"];
        $this->getConnection()->update($this->getMainTable(), $bind, $where);
        return $this;
    }

    /**
     * Join with Customer Grid Flat Table
     */
    public function joinCustomer()
    {
        $joinTable = $this->getTable('customer_grid_flat');
        $this->getSelect()->join($joinTable.' as cgf', 'main_table.seller_id = cgf.entity_id');
    }
}
