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
use Webkul\MpWholesale\Api\PriceRuleRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpWholesale\Model\ResourceModel\PriceRule as PriceRuleResource;
use Webkul\MpWholesale\Model\ResourceModel\PriceRule\CollectionFactory as PriceRuleCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PriceRule Defined Repository
 */
class PriceRuleRepository implements PriceRuleRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $priceRuleFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $priceRuleCollectionFactory;

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
     * @param PriceRuleResource $resource
     * @param PriceRuleFactory $priceRuleFactory
     * @param PriceRuleCollection $priceRuleCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PriceRuleResource $resource,
        PriceRuleFactory $priceRuleFactory,
        PriceRuleCollection $priceRuleCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->priceRuleFactory = $priceRuleFactory;
        $this->priceRuleCollectionFactory = $priceRuleCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Price Rule Complete data
     *
     * @param \Webkul\MpWholesale\Api\Data\PriceRuleInterface $priceRule
     * @return PriceRule
     * @throws CouldNotSaveException
     */
    public function save(Data\PriceRuleInterface $priceRule)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $priceRule->setStoreId($storeId);
        try {
            $this->resource->save($priceRule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $priceRule;
    }

    /**
     * Load Price Rule Complete data by given Block Identity
     *
     * @param string $id
     * @return PriceRule
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $priceRule = $this->priceRuleFactory->create();
        $this->resource->load($priceRule, $id);
        if (!$priceRule->getEntityId()) {
            throw new NoSuchEntityException(__('Price Rule with id "%1" does not exist.', $id));
        }
        return $priceRule;
    }

    /**
     * Delete Price Rule
     *
     * @param \Webkul\MpWholesale\Api\Data\PriceRuleInterface $priceRule
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\PriceRuleInterface $priceRule)
    {
        try {
            $this->resource->delete($priceRule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Price Rule by given Block Identity
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
