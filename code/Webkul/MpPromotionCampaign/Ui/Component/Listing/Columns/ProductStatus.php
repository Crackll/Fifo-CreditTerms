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

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;

/**
 * Class ProductStatus is used to get Promotion Campaign product status
 */
class ProductStatus implements OptionSourceInterface
{
    /**
     * @var \Webkul\MpPromotionCampaign\Model\CampaignProduct
     */
    protected $campaignProduct;

    /**
     * @param \Webkul\MpPromotionCampaign\Model\CampaignProduct $campaignProduct
     */
    public function __construct(
        \Webkul\MpPromotionCampaign\Model\CampaignProduct $campaignProduct
    ) {
        $this->campaignProduct = $campaignProduct;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->campaignProduct->getCampaignProductStatuses();
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
