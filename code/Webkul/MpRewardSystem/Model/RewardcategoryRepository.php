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
use Webkul\MpRewardSystem\Api\RewardcategoryRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardcategory as RewardCategoryResource;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardcategory\CollectionFactory as RewardCategoryCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TimeslotConfigRepository
 *
 */
class RewardcategoryRepository implements RewardcategoryRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;
    /**
     * @var BlockFactory
     */
    protected $rewardCategoryFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @param RewardCategoryResource $resource
     * @param RewardcategoryFactory $rewardCategoryFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RewardCategoryResource $resource,
        RewardcategoryFactory $rewardCategoryFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->rewardCategoryFactory = $rewardCategoryFactory;
        $this->storeManager = $storeManager;
    }
    /**
     * Save Preorder Complete data
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardcategoryInterface $rewardCategory
     * @return rewardCategory
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardcategoryInterface $rewardCategory)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardCategory->setStoreId($storeId);
        try {
            $this->resource->save($rewardCategory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardCategory;
    }

    /**
     * Load Category Reward Complete data by given Block Identity
     *
     * @param string $id
     * @return rewardCategory
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $rewardCategory = $this->rewardCategoryFactory->create();
        $this->resource->load($rewardCategory, $id);
        if (!$rewardCategory->getEntityId()) {
            throw new NoSuchEntityException(__('Reward Category with id "%1" does not exist.', $id));
        }
        return $rewardCategory;
    }

    /**
     * Delete PreorderComplete
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardcategoryInterface $rewardCategory
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardcategoryInterface $rewardCategory)
    {
        try {
            $this->resource->delete($rewardCategory);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Reward Category Data by given Block Identity
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
