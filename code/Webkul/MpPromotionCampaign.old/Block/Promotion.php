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

namespace Webkul\MpPromotionCampaign\Block;

use Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\Grid\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;

class Promotion extends \Magento\Framework\View\Element\Template
{
  /**
   * Campaign
   *
   * @var \Webkul\MpCampaign\Model\CampaignFactory
   */
    public $campaignFactory;

    /**
     * Helper
     *
     * @var \Webkul\MpCampaign\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaignFactory
     * @param \Webkul\MpPromotionCampaign\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaignFactory,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        Context $context,
        array $data = []
    ) {
        $this->campaignFactory = $campaignFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get Offers
     *
     * @return \Webkul\MpCampaign\Model\Campaign\Collection
     */
    public function getOffers()
    {
        $date = $this->helper->getCurrentDateTime();
        $currentDateTime = $this->helper->getDefaultZoneDateTime($date);
        $collection = $this->campaignFactory->create()
                ->getCollection()
                ->addFieldToFilter('start_date', ['lteq'=>$currentDateTime])
                ->addFieldToFilter('end_date', ['gteq'=>$currentDateTime])
                ->addFieldToFilter('status', ['eq'=>CampaignModel::STATUS_ENABLED]);
        $collection->setOrder('start_date', 'desc');
        return $collection;
    }

    /**
     * Get Upcoming Offers
     *
     * @return \Webkul\MpCampaign\Model\Campaign\Collection
     */
    public function getUpcomingOffers()
    {
        $date = $this->helper->getCurrentDateTime();
        $currentDateTime = $this->helper->getDefaultZoneDateTime($date);
        $collection = $this->campaignFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('start_date', ['gteq'=>$currentDateTime])
                        ->addFieldToFilter('end_date', ['gteq'=>$currentDateTime])
                        ->addFieldToFilter('status', ['eq'=>CampaignModel::STATUS_ENABLED]);
        $collection->setOrder('start_date', 'desc');
        return $collection;
    }

    /**
     * Get Banner Image Link
     *
     * @param \Webkul\MpPromotionCampaign\Model\Campaign $campaign
     * @return string|false
     */
    public function getBannerImage($campaign)
    {
        if (!$campaign->getBanner()) {
            return false;
        }
        return $this->helper->getMediaDirectory().$campaign->getBanner();
    }
}
