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

interface RewardcategoryRepositoryInterface
{
    /**
     * Save Preorder Complete data
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardcategoryInterface $rewardCategory
     * @return rewardCategory
     * @throws CouldNotSaveException
     */
    public function save(\Webkul\MpRewardSystem\Api\Data\RewardcategoryInterface $items);
    /**
     * Load Category Reward Complete data by given Block Identity
     *
     * @param string $id
     * @return rewardCategory
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);
    /**
     * Delete PreorderComplete
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardcategoryInterface $rewardCategory
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Webkul\MpRewardSystem\Api\Data\RewardcategoryInterface $item);
    /**
     * Delete Reward Category Data by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id);
}
