<?php
namespace Webkul\MpPromotionCampaign\ViewModel;

class FormViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    private $helperData;

    public function __construct(
        \Magento\Catalog\Helper\Output $catalogHelper,
        \Webkul\MpPromotionCampaign\Helper\Data $mpPromotionCampaignHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->catalogHelper= $catalogHelper;
        $this->mpPromotionCampaignHelper= $mpPromotionCampaignHelper;
        $this->mpHelper= $mpHelper;
    }

    public function getMpPromotionCampaignHelper()
    {
        return $this->mpPromotionCampaignHelper;
    }

    public function getCatalogHelper()
    {
        return $this->catalogHelper;
    }

    public function getMpHelper()
    {
        return $this->mpHelper;
    }
}
