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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;

class ProductStatusOption extends Column
{
    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = 'productStatus';
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['productStatus'] == CampaignProModel::STATUS_JOIN) {
                    $item[$fieldName] =  __('Joined');
                } elseif ($item['productStatus'] == CampaignProModel::STATUS_PENDING) {
                    $item[$fieldName] =  __('Pending');
                } else {
                    $item[$fieldName] =  __('Refused');
                }
            }
        }
        return $dataSource;
    }
}
