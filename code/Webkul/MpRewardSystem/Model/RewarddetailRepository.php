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
use Webkul\MpRewardSystem\Api\RewarddetailRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewarddetail as RewardDetailResource;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewarddetail\CollectionFactory as RewardDetailCollection;
use Magento\Store\Model\StoreManagerInterface;

class RewarddetailRepository implements RewarddetailRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;
    /**
     * @var RewarddetailFactory
     */
    protected $rewardDetailFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * Undocumented function
     *
     * @param RewardDetailResource $resource
     * @param RewarddetailFactory $rewardDetailFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RewardDetailResource $resource,
        RewarddetailFactory $rewardDetailFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->rewardDetailFactory = $rewardDetailFactory;
        $this->storeManager = $storeManager;
    }
    /**
     * Save RewardDetail data
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewarddetailInterface $rewardDetail
     * @return RewardDetail
     * @throws CouldNotSaveException
     */
    public function save(Data\RewarddetailInterface $rewardDetail)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardDetail->setStoreId($storeId);
        try {
            $this->resource->save($rewardDetail);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardDetail;
    }
    /**
     * Load RewardDetail data by given Block Identity
     *
     * @param string $id
     * @return RewardDetail
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $rewardDetail = $this->rewardDetailFactory->create();
        $this->resource->load($rewardDetail, $id);
        if (!$rewardDetail->getEntityId()) {
            throw new NoSuchEntityException(__('Reward Detail with id "%1" does not exist.', $id));
        }
        return $rewardDetail;
    }
    /**
     * Delete RewardDetail
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewarddetailInterface $rewardDetail
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewarddetailInterface $rewardDetail)
    {
        try {
            $this->resource->delete($rewardDetail);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete RewardDetail by given Block Identity
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
