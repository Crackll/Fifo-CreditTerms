<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Model;

use Webkul\MpWholesale\Api\Data;
use Webkul\MpWholesale\Api\ProductRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpWholesale\Model\ResourceModel\Product as ProductResource;
use Webkul\MpWholesale\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class WholeSale ProductRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $productFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $productCollectionFactory;

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
     * @param ProductResource $resource
     * @param ProductFactory $productFactory
     * @param ProductCollection $productCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductResource $resource,
        ProductFactory $productFactory,
        ProductCollection $productCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save WholeSale Product Complete data
     *
     * @param \Webkul\MpWholesale\Api\Data\ProductInterface $product
     * @return Product
     * @throws CouldNotSaveException
     */
    public function save(Data\ProductInterface $product)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $product->setStoreId($storeId);
        try {
            $this->resource->save($product);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $product;
    }

    /**
     * Load WholeSale Product Complete data by given Block Identity
     *
     * @param string $id
     * @return Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $product = $this->productFactory->create();
        $this->resource->load($product, $id);
        if (!$product->getEntityId()) {
            throw new NoSuchEntityException(__('Wholesale Product with id "%1" does not exist.', $id));
        }
        return $product;
    }

    /**
     * Delete WholeSale Product
     *
     * @param \Webkul\MpWholesale\Api\Data\ProductInterface $product
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ProductInterface $product)
    {
        try {
            $this->resource->delete($product);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Product by given Block Identity
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
