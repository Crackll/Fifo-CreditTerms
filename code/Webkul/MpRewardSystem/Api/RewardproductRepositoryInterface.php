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

interface RewardproductRepositoryInterface
{
    /**
     * Save reward product Complete data
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardproductInterface $rewardProduct
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(\Webkul\MpRewardSystem\Api\Data\RewardproductInterface $items);
    /**
     * Load Reward Product Complete data by given Block Identity
     *
     * @param string $id
     * @return rewardProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);
    /**
     * Delete RewardProduct
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardproductInterface $rewardProduct
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Webkul\MpRewardSystem\Api\Data\RewardproductInterface $item);
    /**
     * Delete RewardProduct by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id);
}
