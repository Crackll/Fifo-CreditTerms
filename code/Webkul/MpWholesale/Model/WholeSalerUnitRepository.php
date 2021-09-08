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
use Webkul\MpWholesale\Api\WholeSalerUnitRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpWholesale\Model\ResourceModel\WholeSalerUnit as WholeSalerUnitResource;
use Webkul\MpWholesale\Model\ResourceModel\WholeSalerUnit\CollectionFactory as WholeSalerUnitCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class WholeSale UnitRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WholeSalerUnitRepository implements WholeSalerUnitRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $wholeSalerUnitFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $wholeSalerUnitCollectionFactory;

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
     * @param WholeSalerUnitResource $resource
     * @param WholeSalerUnitFactory $wholeSalerUnitFactory
     * @param WholeSalerUnitCollection $wholeSalerUnitCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        WholeSalerUnitResource $resource,
        WholeSalerUnitFactory $wholeSalerUnitFactory,
        WholeSalerUnitCollection $wholeSalerUnitCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->wholeSalerUnitFactory = $wholeSalerUnitFactory;
        $this->wholeSalerUnitCollectionFactory = $wholeSalerUnitCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save WholeSaler Unit Complete data
     *
     * @param \Webkul\MpWholesale\Api\Data\WholeSalerUnitInterface $wholeSalerUnit
     * @return WholeSalerUnit
     * @throws CouldNotSaveException
     */
    public function save(Data\WholeSalerUnitInterface $wholeSalerUnit)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $wholeSalerUnit->setStoreId($storeId);
        try {
            $this->resource->save($wholeSalerUnit);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $wholeSalerUnit;
    }

    /**
     * Load Wholesaler Unit Complete data by given Block Identity
     *
     * @param string $id
     * @return WholeSalerUnit
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $wholeSalerUnit = $this->wholeSalerUnitFactory->create();
        $this->resource->load($wholeSalerUnit, $id);
        if (!$wholeSalerUnit->getEntityId()) {
            throw new NoSuchEntityException(__('Wholesaler Unit with id "%1" does not exist.', $id));
        }
        return $wholeSalerUnit;
    }

    /**
     * Delete WholeSaler Unit
     *
     * @param \Webkul\MpWholesale\Api\Data\WholeSalerUnitInterface $wholeSalerUnit
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WholeSalerUnitInterface $wholeSalerUnit)
    {
        try {
            $this->resource->delete($wholeSalerUnit);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete WholeSales Unit by given Block Identity
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
