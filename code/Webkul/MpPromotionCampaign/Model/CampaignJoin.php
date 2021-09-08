<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPromotionCampaign\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\MpPromotionCampaign\Api\Data\CampaignJoinInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * MpPromotionCampaign Campaign Model.
 *
 * @method \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignJoin _getResource()
 * @method \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignJoin getResource()
 */
class CampaignJoin extends AbstractModel implements CampaignJoinInterface, IdentityInterface
{
  /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Marketplace Campaign cache tag.
     */
    const CACHE_TAG = 'mppromotionseller_campaign';

    /**
     * @var string
     */
    public $_cacheTag = 'mppromotionseller_campaign';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    public $_eventPrefix = 'mppromotionseller_campaign';

    /**
     * Campaign Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Initialize resource model.
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignJoin::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteCampaign();
        }

        return parent::load($id, $field);
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpPromotionCampaign\Api\Data\CampaignJoinInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
     /**
      * Get ID.
      *
      * @return int
      */
    public function getCampaignId()
    {
        return parent::getData(self::CAMPAIGN_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpPromotionCampaign\Api\Data\CampaignJoinInterface
     */
    public function setCampaignId($id)
    {
        return $this->setData(self::CAMPAIGN_ID, $id);
    }
     /**
      * Get ID.
      *
      * @return int
      */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpPromotionCampaign\Api\Data\CampaignJoinInterface
     */
    public function setSellerId($id)
    {
        return $this->setData(self::SELLER_ID, $id);
    }

    /**
     * Load No-Route Campaign.
     *
     * @return \Webkul\MpPromotionCampaign\Model\Campaign
     */
    public function noRouteCampaign()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get Campaign Statuses
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
