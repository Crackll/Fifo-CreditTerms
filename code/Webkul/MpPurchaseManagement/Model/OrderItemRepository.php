<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Model;

use Webkul\MpPurchaseManagement\Api\Data;
use Webkul\MpPurchaseManagement\Api\OrderItemRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpPurchaseManagement\Model\ResourceModel\OrderItem as OrderItemResource;
use Webkul\MpPurchaseManagement\Model\ResourceModel\OrderItem\CollectionFactory as OrderItemCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OrderItemRepository Model
 */
class OrderItemRepository implements OrderItemRepositoryInterface
{
    /**
     * @var OrderItemResource
     */
    protected $resource;

    /**
     * @var OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var OrderItemCollection
     */
    protected $orderItemCollectionFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param OrderItemResource           $resource
     * @param OrderItemFactory            $orderItemFactory
     * @param OrderItemCollection         $orderItemCollectionFactory
     * @param DataObjectHelper            $dataObjectHelper
     * @param DataObjectProcessor         $dataObjectProcessor
     * @param StoreManagerInterface       $storeManager
     */
    public function __construct(
        OrderItemResource $resource,
        OrderItemFactory $orderItemFactory,
        OrderItemCollection $orderItemCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Order Item Complete data
     *
     * @param \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface $orderItem
     * @return OrderItem
     * @throws CouldNotSaveException
     */
    public function save(Data\OrderItemInterface $orderItem)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $orderItem->setStoreId($storeId);
        try {
            $this->resource->save($orderItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $orderItem;
    }

    /**
     * Load Order Item Complete data by given Block Identity
     *
     * @param string $id
     * @return OrderItem
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $orderItem = $this->orderItemFactory->create();
        $this->resource->load($orderItem, $id);
        if (!$orderItem->getEntityId()) {
            throw new NoSuchEntityException(__('Order Item with id "%1" does not exist.', $id));
        }
        return $orderItem;
    }

    /**
     * Delete Order Item
     *
     * @param \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface $orderItem
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\OrderItemInterface $orderItem)
    {
        try {
            $this->resource->delete($orderItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Order Item by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
