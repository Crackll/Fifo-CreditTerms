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
namespace Webkul\MpRewardSystem\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface RewarddetailRepositoryInterface
{
    /**
     * Save RewardDetail data
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewarddetailInterface $rewardDetail
     * @return RewardDetail
     * @throws CouldNotSaveException
     */
    public function save(\Webkul\MpRewardSystem\Api\Data\RewarddetailInterface $items);
    /**
     * Load RewardDetail data by given Block Identity
     *
     * @param string $id
     * @return RewardDetail
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);
    /**
     * Delete RewardDetail
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewarddetailInterface $rewardDetail
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Webkul\MpRewardSystem\Api\Data\RewarddetailInterface $item);
    /**
     * Delete RewardDetail by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id);
}
