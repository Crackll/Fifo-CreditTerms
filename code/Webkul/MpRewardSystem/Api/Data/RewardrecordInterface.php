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

namespace Webkul\MpRewardSystem\Api\Data;

interface RewardrecordInterface
{
    const ENTITY_ID                 = 'entity_id';
    const CUSTOMER_ID               = 'customer_id';
    const SELLER_ID                 = 'seller_id';
    const TOTAL_REWARD_POINT        = 'total_reward_point';
    const REMAINING_REWARD_POINT    = 'remaining_reward_point';
    const USED_REWARD_POINT         = 'used_reward_point';
    const UPDATED_AT                = 'updated_at';
    const PENDING_REWARD            = 'pending_reward';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Get Total Reward Points
     *
     * @return int|null
     */
    public function getTotalRewardPoint();

    /**
     * Get Remaining Reward Points
     *
     * @return int|null
     */
    public function getRemainingRewardPoint();

    /**
     * Get Used Reward Points
     *
     * @return int|null
     */
    public function getUsedRewardPoint();

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt();
    /**
     * Get Pending Reward
     *
     * @return string|null
     */
    public function getPendingReward();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setEntityId($id);

    /**
     * Set Customer ID
     *
     * @return int|null
     */
    public function setCustomerId($customerId);

    /**
     * Set Seller ID
     *
     * @return int|null
     */
    public function setSellerId($sellerId);

    /**
     * Set Total Reward Point
     *
     * @return int|null
     */
    public function setTotalRewardPoint($point);

    /**
     * Set Remaining Reward Total
     *
     * @return int|null
     */
    public function setRemainingRewardPoint($point);

    /**
     * Set Used Reward Point
     *
     * @return int|null
     */
    public function setUsedRewardPoint($point);

    /**
     * Set Updated At
     *
     * @return string|null
     */
    public function setUpdatedAt($date);
    /**
     * Set Pending Reward
     *
     * @return string|null
     */
    public function setPendingReward($pendingReward);
}
