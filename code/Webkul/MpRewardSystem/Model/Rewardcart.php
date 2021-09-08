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

use Webkul\MpRewardSystem\Api\Data\RewardcartInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewardcart extends AbstractModel implements RewardcartInterface, IdentityInterface
{
    const CACHE_TAG = 'mprewardsystem_rewardcart';
    /**
     * @var string
     */
    protected $cacheTag = 'mprewardsystem_rewardcart';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $eventPrefix = 'mprewardsystem_rewardcart';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRewardSystem\Model\ResourceModel\Rewardcart::class);
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
     * @param  $id
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
