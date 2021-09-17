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
use Webkul\MpPromotionCampaign\Api\Data\CampaignInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * MpPromotionCampaign Campaign Model.
 *
 * @method \Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign _getResource()
 * @method \Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign getResource()
 */
class Campaign extends AbstractModel implements CampaignInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Marketplace Campaign cache tag.
     */
    const CACHE_TAG = 'mppromotioncampaign_campaigns';

    /**
     * @var string
     */
    public $_cacheTag = 'mppromotioncampaign_campaigns';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    public $_eventPrefix = 'mppromotioncampaign_campaigns';

    /**
     * Campaign Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const CAMPAIGN_STATUS_RUNNING = '1';
    const CAMPAIGN_STATUS_COMMINGSOON = '2';
    const CAMPAIGN_STATUS_EXPIRED = '3';

    /**
     * Initialize resource model.
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign::class);
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
     * @return \Webkul\MpPromotionCampaign\Api\Data\CampaignInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
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
