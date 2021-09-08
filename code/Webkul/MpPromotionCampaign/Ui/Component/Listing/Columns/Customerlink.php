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

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\Marketplace\Helper\Data;

class Customerlink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

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
        Data $helper,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
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
            $fieldName = 'seller_id';
            foreach ($dataSource['data']['items'] as &$item) {
                $sellerDetail =  $this->helper->getSellerProductDataByProductId($item['entity_id'])->getFirstItem();
                if (isset($item['entity_id'])) {
                    if (isset($sellerDetail['seller_id'])) {
                        $item[$fieldName] = "<a href='".$this->urlBuilder
                        ->getUrl('customer/index/edit', ['id' => $sellerDetail['seller_id'], 'seller_panel' => 1])."'
                         target='blank' title='".__('View Customer')."'>".$sellerDetail->getName()."</a>";
                    } else {
                        $item[$fieldName] = __("Admin");
                    }
                }
            }
        }
        return $dataSource;
    }
}
