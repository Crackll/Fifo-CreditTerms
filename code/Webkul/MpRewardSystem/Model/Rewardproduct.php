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

use Webkul\MpRewardSystem\Api\Data\RewardproductInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewardproduct extends AbstractModel implements RewardproductInterface, IdentityInterface
{
    const CACHE_TAG = 'mprewardsystem_rewardproduct';
    /**
     * @var string
     */
    protected $cacheTag = 'mprewardsystem_rewardproduct';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $eventPrefix = 'mprewardsystem_rewardproduct';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRewardSystem\Model\ResourceModel\Rewardproduct::class);
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
     * @param $id
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
    /**
     * get product id
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }
    /**
     * set product id
     *
     * @param $productId
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
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
     * set seller id
     *
     * @param $sellerId
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }
    /**
     * get points
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
}
