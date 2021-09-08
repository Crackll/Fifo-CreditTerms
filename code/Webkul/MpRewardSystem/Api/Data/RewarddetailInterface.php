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

interface RewarddetailInterface
{
    const ENTITY_ID         = 'entity_id';
    const CUSTOMER_ID       = 'customer_id';
    const SELLER_ID         = 'seller_id';
    const REWARD_POINT      = 'reward_point';
    const AMOUNT            = 'amount';
    const STATUS            = 'status';
    const ACTION            = 'action';
    const ORDER_ID          = 'order_id';
    const ITEM_IDS          = 'item_ids';
    const IS_REVERT         = 'is_revert';
    const REWARD_TYPE       = 'reward_type';
    const TRANSACTION_AT    = 'transaction_at';
    const CURRENCY_CODE     = 'currency_code';
    const CURR_AMOUNT       = 'curr_amount';
    const TRANSACTION_NOTE  = 'transaction_note';
    const REVIEW_ID         = 'review_id';
    const PENDING_REWARD    = 'pending_reward';

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
     * Get Reward Point
     *
     * @return int|null
     */
    public function getRewardPoint();

    /**
     * Get Amount
     *
     * @return float|null
     */
    public function getAmount();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Action
     *
     * @return int|null
     */
    public function getAction();

    /**
     * Get Order id
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Get ItemIds
     *
     * @return string|null
     */
    public function getItemIds();

    /**
     * Get IsRevert
     *
     * @return int|null
     */
    public function getIsRevert();

    /**
     * Get rewardType
     *
     * @return string|null
     */
    public function getRewardType();

    /**
     * Get Tranction Date
     *
     * @return string|null
     */
    public function getTransactionAt();

    /**
     * Get Currency Code
     *
     * @return string|null
     */
    public function getCurrencyCode();

    /**
     * Get Current Amount
     *
     * @return float|null
     */
    public function getCurrAmount();

    /**
     * Get Transaction Note
     *
     * @return string|null
     */
    public function getTransactionNote();

    /**
     * Get Review Id
     *
     * @return int|null
     */
    public function getReviewId();
    /**
     * Get Pending Reward
     *
     * @return int|null
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
     * Set Reward Point
     *
     * @return int|null
     */
    public function setRewardPoint($point);

    /**
     * Set Amount
     *
     * @return float|null
     */
    public function setAmount($amount);

    /**
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);

    /**
     * Set Action
     *
     * @return int|null
     */
    public function setAction($action);

    /**
     * Set Order Id
     *
     * @return int|null
     */
    public function setOrderId($orderId);

    /**
     * Set IsRevert
     *
     * @return int|null
     */
    public function setIsRevert($isRevert);

    /**
     * Set Item Ids
     *
     * @return string|null
     */
    public function setItemIds($itemIds);

    /**
     * Set RewardType
     *
     * @return string|null
     */
    public function setRewardType($rewardType);

    /**
     * Set Tranction Date
     *
     * @return string|null
     */
    public function setTransactionAt($date);

    /**
     * Set Currency Code
     *
     * @return string|null
     */
    public function setCurrencyCode($code);

    /**
     * Set Current Amount
     *
     * @return float|null
     */
    public function setCurrAmount($amount);

    /**
     * Set Transaction Note
     *
     * @return string|null
     */
    public function setTransactionNote($note);
    /**
     * Set Review Id
     *
     * @return int|null
     */
    public function setReviewId($reviewId);
    /**
     * Set Pending Reward
     *
     * @return int|null
     */
    public function setPendingReward($pendingReward);
}
