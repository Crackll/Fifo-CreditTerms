<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Model;

use Webkul\MpRewardSystem\Api\Data;
use Webkul\MpRewardSystem\Api\RewardrecordRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardrecord as RewardRecordResource;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardrecord\CollectionFactory as RewardRecordCollection;
use Magento\Store\Model\StoreManagerInterface;

class RewardrecordRepository implements RewardrecordRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var RewardrecordFactory
     */
    protected $rewardRecordFactory;

    /**
     * @var RewardRecordCollection
     */
    protected $rewardRecordCollectionFactory;

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
    protected $storeManager;
    /**
     * @param RewardRecordResource $resource
     * @param RewardrecordFactory $rewardRecordFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RewardRecordResource $resource,
        RewardrecordFactory $rewardRecordFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->rewardRecordFactory = $rewardRecordFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Reward Record data
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardrecordInterface $rewardrecord
     * @return Rewardrecord
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardrecordInterface $rewardRecord)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardRecord->setStoreId($storeId);
        try {
            $this->resource->save($rewardRecord);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardRecord;
    }

    /**
     * Load Reward Record data by given Block Identity
     *
     * @param string $id
     * @return Rewardrecord
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $rewardRecord = $this->rewardRecordFactory->create();
        $this->resource->load($rewardRecord, $id);
        if (!$rewardRecord->getEntityId()) {
            throw new NoSuchEntityException(__('Reward record with id "%1" does not exist.', $id));
        }
        return $rewardRecord;
    }

    /**
     * Delete Reward Record
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\RewardrecordInterface $rewardRecord
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardrecordInterface $rewardRecord)
    {
        try {
            $this->resource->delete($rewardRecord);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Reward Record by given Block Identity
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
