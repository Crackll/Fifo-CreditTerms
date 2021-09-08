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

use Webkul\MpRewardSystem\Api\Data\RewarddetailInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewarddetail extends AbstractModel implements RewarddetailInterface, IdentityInterface
{
    const CACHE_TAG = 'mprewardsystem_rewarddetail';
    /**
     * @var string
     */
    protected $cacheTag = 'mprewardsystem_rewarddetail';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $eventPrefix = 'mprewardsystem_rewarddetail';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRewardSystem\Model\ResourceModel\Rewarddetail::class);
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    /**
     * Set Id
     *
     * @param $id
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
    /**
     * get customer id
     *
     * @return in|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }
    /**
     * set customer Id
     *
     * @param $customerId
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
    /**
     * get seller Id
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }
    /**
     * set seller id
     *
     * @param $sellerId
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }
    /**
     * get reward poiny
     *
     * @return float|null
     */
    public function getRewardPoint()
    {
        return $this->getData(self::REWARD_POINT);
    }
    /**
     * set reward point
     *
     * @param $point
     */
    public function setRewardPoint($point)
    {
        return $this->setData(self::REWARD_POINT, $point);
    }
    /**
     * get amount
     *
     * @return float|amount
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }
    /**
     * set amount
     *
     * @param $amount
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }
    /**
     * get status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    /**
     * set status
     *
     * @param $status
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
    /**
     * get Action
     *
     * @return string|null
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }
    /**
     * set action
     *
     * @param $action
     */
    public function setAction($action)
    {
        return $this->setData(self::ACTION, $action);
    }
    /**
     * get order id
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }
    /**
     * set order id
     *
     * @param $orderId
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }
    /**
     * get item id
     *
     * @return void
     */
    public function getItemIds()
    {
        return $this->getData(self::ITEM_IDS);
    }
    /**
     * set item ids
     *
     * @param $itemIds
     */
    public function setItemIds($itemIds)
    {
        return $this->setData(self::ITEM_IDS, $itemIds);
    }
    /**
     * get is revert
     *
     * @return int
     */
    public function getIsRevert()
    {
        return $this->getData(self::IS_REVERT);
    }
    /**
     * set is revert
     *
     * @param $isRevert
     */
    public function setIsRevert($isRevert)
    {
        return $this->setData(self::IS_REVERT, $isRevert);
    }
    /**
     * get reward type
     *
     * @return string|null
     */
    public function getRewardType()
    {
        return $this->getData(self::REWARD_TYPE);
    }
    /**
     * set reward type
     *
     * @param $rewardType
     */
    public function setRewardType($rewardType)
    {
        return $this->setData(self::REWARD_TYPE, $rewardType);
    }
    /**
     * get transaction at
     *
     * @return string|null
     */
    public function getTransactionAt()
    {
        return $this->getData(self::TRANSACTION_AT);
    }
    /**
     * set transaction at
     *
     * @param $date
     */
    public function setTransactionAt($date)
    {
        return $this->setData(self::TRANSACTION_AT, $date);
    }
    /**
     * get currency code
     *
     * @return string|null
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }
    /**
     * set currency code
     *
     * @param $code
     */
    public function setCurrencyCode($code)
    {
        return $this->setData(self::CURRENCY_CODE, $code);
    }
    /**
     * get current amount
     *
     * @return float|null
     */
    public function getCurrAmount()
    {
        return $this->getData(self::CURR_AMOUNT);
    }
    /**
     * set current amount
     *
     * @param $amount
     */
    public function setCurrAmount($amount)
    {
        return $this->setData(self::CURR_AMOUNT, $amount);
    }
    /**
     * get transaction note
     *
     * @return string
     */
    public function getTransactionNote()
    {
        return $this->getData(self::TRANSACTION_NOTE);
    }
    /**
     * set transaction note
     *
     * @param $note
     */
    public function setTransactionNote($note)
    {
        return $this->setData(self::TRANSACTION_NOTE, $note);
    }
    /**
     * get review id
     *
     * @return int|null
     */
    public function getReviewId()
    {
        return $this->getData(self::REVIEW_ID);
    }
    /**
     * set review id
     *
     * @param $reviewId
     */
    public function setReviewId($reviewId)
    {
        return $this->setData(self::REVIEW_ID, $reviewId);
    }
    /**
     * get pending reward
     *
     * @return float|null
     */
    public function getPendingReward()
    {
        return $this->getData(self::PENDING_REWARD);
    }
    /**
     * set pending reward
     *
     * @param $pendingReward
     */
    public function setPendingReward($pendingReward)
    {
        return $this->setData(self::PENDING_REWARD, $pendingReward);
    }
}
