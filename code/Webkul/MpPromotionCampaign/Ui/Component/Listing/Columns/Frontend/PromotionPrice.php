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

class PromotionPrice extends Column
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
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaign,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->campaign = $campaign;
        $this->request = $request;
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
        $campaignId = '';
        $campaignId =  $this->request->getParam('id');
        if (empty($campaignId)) {
            $url = $this->redirect->getRefererUrl();
            $urlData = $this->helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
        }
     
        $campainDetail =  $this->campaign->create()->getCollection()
        ->addFieldToFilter('entity_id', $campaignId)
        ->getFirstItem()
        ->getData();
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['sellerPrice'])) {
                    $promotionPrice = $item['sellerPrice'];
                } else {
                    if (isset($item['price'])) {
                        $symbol = substr($item['price'], 0, 1);
                        $price = substr($item['price'], 1);
                        $price = substr($price, 0, -3);
                        $price = (int) preg_replace('/[^0-9]/', '', $price);
                        $promotionPrice = ceil(($price * $campainDetail['discount'])/100);
                        $promotionPrice = $price-$promotionPrice;
                    }
                }
                if (isset($promotionPrice)) {
                    $item[$this->getData('name')] = $promotionPrice;
                }
            }
        }
        return $dataSource;
    }
}
