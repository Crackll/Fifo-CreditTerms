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

use Webkul\MpRewardSystem\Api\Data\RewardrecordInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewardrecord extends AbstractModel implements RewardrecordInterface, IdentityInterface
{
    const CACHE_TAG = 'mprewardsystem_rewardrecord';
    /**
     * @var string
     */
    protected $cacheTag = 'mprewardsystem_rewardrecord';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $eventPrefix = 'mprewardsystem_rewardrecord';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRewardSystem\Model\ResourceModel\Rewardrecord::class);
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
     * set entity id
     *
     * @param  $id
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
    /**
     * get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }
    /**
     * set customer id
     *
     * @param $customerId
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
    /**
     * get seller id
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }
    /**
     * set seller ide
     *
     * @param $sellerId
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }
    /**
     * get total reward point
     *
     * @return float|null
     */
    public function getTotalRewardPoint()
    {
        return $this->getData(self::TOTAL_REWARD_POINT);
    }
    /**
     * set total reward point
     *
     * @param $point
     */
    public function setTotalRewardPoint($point)
    {
        return $this->setData(self::TOTAL_REWARD_POINT, $point);
    }
    /**
     * get remaining reward point
     *
     * @return float|null
     */
    public function getRemainingRewardPoint()
    {
        return $this->getData(self::REMAINING_REWARD_POINT);
    }
    /**
     * set remaining reward point
     *
     * @param $point
     */
    public function setRemainingRewardPoint($point)
    {
        return $this->setData(self::REMAINING_REWARD_POINT, $point);
    }
    /**
     * get used reward point
     *
     * @return float|null
     */
    public function getUsedRewardPoint()
    {
        return $this->getData(self::USED_REWARD_POINT);
    }
    /**
     * set used reward point
     *
     * @param $point
     */
    public function setUsedRewardPoint($point)
    {
        return $this->setData(self::USED_REWARD_POINT, $point);
    }
    /**
     * get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }
    /**
     * set updated at
     *
     * @param $date
     */
    public function setUpdatedAt($date)
    {
        return $this->setData(self::UPDATED_AT, $date);
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
     * set prnding reward
     *
     * @param $pendingReward
     */
    public function setPendingReward($pendingReward)
    {
        return $this->setData(self::PENDING_REWARD, $pendingReward);
    }
}
