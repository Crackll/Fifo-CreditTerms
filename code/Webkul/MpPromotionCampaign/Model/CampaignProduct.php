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
use Webkul\MpPromotionCampaign\Api\Data\CampaignProductInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * MpPromotionCampaign Campaign Product Model.
 *
 * @method \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct _getResource()
 * @method \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct getResource()
 */
class CampaignProduct extends AbstractModel implements CampaignProductInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Marketplace Campaign cache tag.
     */
    const CACHE_TAG = 'mppromotionseller_product_campaign';

    /**
     * @var string
     */
    public $_cacheTag = 'mppromotionseller_product_campaign';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    public $_eventPrefix = 'mppromotionseller_product_campaign';

    /**
     * Campaign Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const STATUS_PENDING = 1;
    const STATUS_REFUSE = 2;
    const STATUS_JOIN = 3;

    /**
     * Initialize resource model.
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct::class);
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
     * @return \Webkul\MpPromotionCampaign\Api\Data\CampaignProductInterface
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

    /**
     * Get Campaign Product Statuses.
     *
     * @return array
     */
    public function getCampaignProductStatuses()
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_REFUSE => __('Refused'),
            self::STATUS_JOIN => __('Joined'),
        ];
    }
}
