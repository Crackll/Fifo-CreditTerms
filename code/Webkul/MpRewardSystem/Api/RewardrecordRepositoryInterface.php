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

interface RewardrecordRepositoryInterface
{
    /**
     * Save Reward Record data
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardrecordInterface $rewardrecord
     * @return Rewardrecord
     * @throws CouldNotSaveException
     */
    public function save(\Webkul\MpRewardSystem\Api\Data\RewardrecordInterface $items);
    /**
     * Load Reward Record data by given Block Identity
     *
     * @param string $id
     * @return Rewardrecord
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);
    /**
     * Delete Reward Record
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\RewardrecordInterface $rewardRecord
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Webkul\MpRewardSystem\Api\Data\RewardrecordInterface $item);
    /**
     * Delete Reward Record by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id);
}
