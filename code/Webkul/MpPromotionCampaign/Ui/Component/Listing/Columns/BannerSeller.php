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

class BannerSeller extends Column
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager =  $storeManager;
        $this->timezone = $timezone;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    public function getConfig()
    {
        return $this->storeManager->getStore()->getConfig('catalog/placeholder/thumbnail_placeholder');
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
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['banner'])) {
                    $item[$fieldName . '_src'] = $mediaUrl.$item['banner'];
                    $item[$fieldName . '_orig_src'] = $mediaUrl.$item['banner'];
                } else {
                    $item[$fieldName . '_src'] = $mediaUrl.'catalog/product/placeholder/'.$this->getConfig();
                    $item[$fieldName . '_orig_src'] = $mediaUrl.'catalog/product/placeholder/'.$this->getConfig();
                }
            }
        }
        return $dataSource;
    }
}
