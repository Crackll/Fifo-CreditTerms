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

namespace Webkul\MpPromotionCampaign\Block\Promotion;

use Magento\Framework\View\Element\Template\Context;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * Filter Provider
     *
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    public $filterProvider;
    
    /**
     * Campaign
     *
     * @var \Webkul\MpPromotionCampaign\Model\CampaignFactory
     */
    public $campaignFactory;

    /**
     * Helper
     *
     * @var \Webkul\MpPromotionCampaign\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaignFactory
     * @param \Webkul\MpPromotionCampaign\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaignFactory,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->filterProvider = $filterProvider;
        $this->campaignFactory = $campaignFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getOffer()
    {
        $campaignId = $this->getRequest()->getParam('id');
        $collection = $this->campaignFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('entity_id', $campaignId);
        $campaign = $collection->getFirstItem();
        return $campaign;
    }

    /**
     * Banner Image Src
     *
     * @param \Webkul\MpCampaign\Model\Campaign $campaign
     * @return string|false
     */
    public function getBannerImage($campaign)
    {
        if (!$campaign->getBanner()) {
            return false;
        }
        return $this->helper->getMediaDirectory().$campaign->getBanner();
    }

    /**
     * Filter data
     *
     * @param string $data
     * @return string
     */
    public function getEditorContent($data)
    {
        return $this->filterProvider->getPageFilter()->filter($data);
    }
}
