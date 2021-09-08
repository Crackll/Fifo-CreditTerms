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
use Webkul\MpPurchaseManagement\Api\OrderRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpPurchaseManagement\Model\ResourceModel\Order as OrderResource;
use Webkul\MpPurchaseManagement\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OrderRepository Model
 */
class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var OrderResource
     */
    protected $resource;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderCollection
     */
    protected $orderCollectionFactory;

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
     * @param OrderResource           $resource
     * @param OrderFactory            $orderFactory
     * @param OrderCollection         $orderCollectionFactory
     * @param DataObjectHelper        $dataObjectHelper
     * @param DataObjectProcessor     $dataObjectProcessor
     * @param StoreManagerInterface   $storeManager
     */
    public function __construct(
        OrderResource $resource,
        OrderFactory $orderFactory,
        OrderCollection $orderCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Order Complete data
     *
     * @param \Webkul\MpPurchaseManagement\Api\Data\OrderInterface $order
     * @return Order
     * @throws CouldNotSaveException
     */
    public function save(Data\OrderInterface $order)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $order->setStoreId($storeId);
        try {
            $this->resource->save($order);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $order;
    }

    /**
     * Load Order Complete data by given Block Identity
     *
     * @param string $id
     * @return Order
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $order = $this->orderFactory->create();
        $this->resource->load($order, $id);
        if (!$order->getEntityId()) {
            throw new NoSuchEntityException(__('Order with id "%1" does not exist.', $id));
        }
        return $order;
    }

    /**
     * Delete Order
     *
     * @param \Webkul\MpPurchaseManagement\Api\Data\OrderInterface $order
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\OrderInterface $order)
    {
        try {
            $this->resource->delete($order);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Order by given Block Identity
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
