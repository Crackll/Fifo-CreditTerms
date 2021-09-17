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

namespace Webkul\MpPromotionCampaign\Ui\Component\Listing\Columns;

use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;

class CampaignStatusOption implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * To array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $runningStatus = \Webkul\MpPromotionCampaign\Helper\Data::$runningStatus;
        return [
            [
                'value' => CampaignModel::CAMPAIGN_STATUS_COMMINGSOON,
                'row_label' => $runningStatus[0].__('Upcoming'),
                'label' => __('Coming soon')
            ],
            [
                'value' => CampaignModel::CAMPAIGN_STATUS_RUNNING,
                'row_label' => $runningStatus[1].__('Live'),
                'label' => __('Live')
            ],
            [
                'value' => CampaignModel::CAMPAIGN_STATUS_EXPIRED,
                'row_label' => $runningStatus[2].__('Expired'),
                'label' => __('Expired')
            ],
        ];
    }
}
