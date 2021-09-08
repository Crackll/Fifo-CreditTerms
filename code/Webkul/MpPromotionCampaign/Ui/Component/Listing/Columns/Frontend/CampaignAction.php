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

namespace Webkul\MpPromotionCampaign\Ui\Component\Listing\Columns\Frontend;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CampaignAction extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->helper  =$helper;
        $this->urlBuilder = $urlBuilder;
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
            $fieldName = $this->getData('name');
            
            foreach ($dataSource['data']['items'] as &$item) {
                $campaignJoin = '0';
                $campaignJoin = $this->helper->campaignSellerStatus($item['entity_id']);
                if ($campaignJoin != 0) {
                    $campaignStatus = $this->helper->campaignStatus($item['entity_id']);
                    if ($campaignStatus['code'] == 2) {
                         $item[$fieldName.'_html'] = "<button class='button'><span>".__('Cancel ')."</span></button>";
                         $item[$fieldName.'_title'] = __('Do you want to cancel your promotion ?');
                         $item[$fieldName.'_submitlabel'] = __('Confirm');
                         $item[$fieldName.'_cancellabel'] = __('Back ');
                         $item[$fieldName.'_campaignId'] = $item['entity_id'];
                         $item[$fieldName.'_formaction'] = $this->urlBuilder
                                                            ->getUrl('mppromotioncampaign/campaign/cancelcampaign/');
                    }
                } else {
                    $campaignStatus = $this->helper->campaignStatus($item['entity_id']);
                    if ($campaignStatus['code'] == 2) {
                        $item[$fieldName.'_html'] = "<button class='button'><span>".__('Join')."</span></button>";
                        $item[$fieldName.'_title'] = __('Do you want to join promotion ?');
                        $item[$fieldName.'_submitlabel'] = __('Confirm');
                        $item[$fieldName.'_cancellabel'] = __('Back ');
                        $item[$fieldName.'_campaignId'] = $item['entity_id'];
                        $item[$fieldName.'_formaction'] = $this->urlBuilder
                                                          ->getUrl('mppromotioncampaign/campaign/SellerJoin/');
                    }
                }
            }
        }
        return $dataSource;
    }
}
