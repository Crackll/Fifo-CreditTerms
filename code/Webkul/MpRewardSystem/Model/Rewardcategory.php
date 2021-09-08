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

use Webkul\MpRewardSystem\Api\Data\RewardcategoryInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewardcategory extends AbstractModel implements RewardcategoryInterface, IdentityInterface
{
    const CACHE_TAG = 'mprewardsystem_rewardcategory';
    /**
     * @var string
     */
    protected $cacheTag = 'mprewardsystem_rewardcategory';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $eventPrefix = 'mprewardsystem_rewardcategory';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRewardSystem\Model\ResourceModel\Rewardcategory::class);
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
     * Get category Id
     *
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }
    /**
     * Set category Id
     *
     * @param categoryId
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }
    /**
     * Get Seller Id
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }
    /**
     * Set Seller Id
     *
     * @param $sellerId
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }
    /**
     * Get Points
     *
     * @return float|null
     */
    public function getPoints()
    {
        return $this->getData(self::POINTS);
    }
    /**
     * set points
     *
     * @param $point
     */
    public function setPoints($point)
    {
        return $this->setData(self::POINTS, $point);
    }
    /**
     * Get Status
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
}
