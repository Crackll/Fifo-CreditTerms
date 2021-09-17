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

namespace Webkul\MpPromotionCampaign\Model\Campaign\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Webkul\MpPromotionCampaign\Model\Campaign
     */
    public $marketplaceCampaign;

    /**
     * Constructor
     *
     * @param \Webkul\MpPromotionCampaign\Model\Campaign $marketplaceCampaign
     */
    public function __construct(\Webkul\MpPromotionCampaign\Model\Campaign $marketplaceCampaign)
    {
        $this->marketplaceCampaign = $marketplaceCampaign;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->marketplaceCampaign->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
