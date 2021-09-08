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
use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;

class CampaignStatus extends Column
{
    /**
     * TimeZone
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $timezone;

    /**
     * Helper
     *
     * @var \Webkul\MpPromotionCampaign\Helper\Data
     */
    public $helper;
    
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Webkul\MpPromotionCampaign\Helper\Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

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
            $date = $this->helper->getCurrentDateTime();
            $currentDateTime = $this->helper->getDefaultZoneDateTime($date);
            $currentTimeStamp = strtotime($currentDateTime);
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $time =  explode('To', $item['start_date']);
                    $startTimeStamp = strtotime($time[0]);
                    $endTimeStamp = strtotime($item['end_date']);
                    $status = 0;
                  
                    if ($endTimeStamp > $currentTimeStamp && $startTimeStamp > $currentTimeStamp) {
                        $status = CampaignModel::CAMPAIGN_STATUS_COMMINGSOON;
                    } elseif ($startTimeStamp < $currentTimeStamp && $endTimeStamp > $currentTimeStamp) {
                        $status = CampaignModel::CAMPAIGN_STATUS_RUNNING;
                    } elseif ($endTimeStamp < $currentTimeStamp) {
                        $status = CampaignModel::CAMPAIGN_STATUS_EXPIRED;
                    }
                    $item[$this->getData('name')] = $status;
                }
            }
        }
        return $dataSource;
    }
}
