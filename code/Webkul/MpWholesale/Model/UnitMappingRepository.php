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

namespace Webkul\MpWholesale\Model;

use Webkul\MpWholesale\Api\Data;
use Webkul\MpWholesale\Api\UnitMappingRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpWholesale\Model\ResourceModel\UnitMapping as UnitMappingResource;
use Webkul\MpWholesale\Model\ResourceModel\UnitMapping\CollectionFactory as UnitMappingCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UnitMappingRepository WholeSale
 */
class UnitMappingRepository implements UnitMappingRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $unitMappingFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $unitMappingCollectionFactory;

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
     * @param UnitMappingResource $resource
     * @param UnitMappingFactory $unitMappingFactory
     * @param UnitMappingCollection $unitMappingCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UnitMappingResource $resource,
        UnitMappingFactory $unitMappingFactory,
        UnitMappingCollection $unitMappingCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->unitMappingFactory = $unitMappingFactory;
        $this->unitMappingCollectionFactory = $unitMappingCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Unit Mapping Complete data
     *
     * @param \Webkul\MpWholesale\Api\Data\UnitMappingInterface $unitMapping
     * @return UnitMapping
     * @throws CouldNotSaveException
     */
    public function save(Data\UnitMappingInterface $unitMapping)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $unitMapping->setStoreId($storeId);
        try {
            $this->resource->save($unitMapping);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $unitMapping;
    }

    /**
     * Load Unit Mapping Complete data by given Block Identity
     *
     * @param string $id
     * @return UnitMapping
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $unitMapping = $this->unitMappingFactory->create();
        $this->resource->load($unitMapping, $id);
        if (!$unitMapping->getEntityId()) {
            throw new NoSuchEntityException(__('Unit with id "%1" does not exist.', $id));
        }
        return $unitMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getByPriceRuleId($priceRuleId)
    {
        $unitMappingCollection = $this->unitMappingCollectionFactory->create()
                ->addFieldToFilter('rule_id', $priceRuleId);
        $unitMappingCollection->load();

        return $unitMappingCollection;
    }

    /**
     * Delete Unit Mapping
     *
     * @param \Webkul\MpWholesale\Api\Data\UnitMappingInterface $unitMapping
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\UnitMappingInterface $unitMapping)
    {
        try {
            $this->resource->delete($unitMapping);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Unit Mapping by given Block Identity
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
