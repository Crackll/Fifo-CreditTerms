<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Price extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * @var  \Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @var  \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;
    
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param \Webkul\MpPriceList\Helper\Data $priceListHelper
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        \Webkul\MpPriceList\Helper\Data $priceListHelper,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $components = [],
        array $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->currency = $currency;
        $this->priceFormatter = $priceFormatter;
        $this->priceListHelper     =   $priceListHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $currentCurrencyCode = $this->priceListHelper->getCurrentCurrencyCode();
                if ($item['price_type'] == 1) {
                    $currentCurrencyCode = $this->priceListHelper->getCurrentCurrencyCode();
                    $item[$this->getData('name')] = $this->priceFormatter->format(
                        $item[$this->getData('name')],
                        false,
                        null,
                        null,
                        $currentCurrencyCode
                    );
                    $amount = $this->currency->format($item['amount'], ['display'=>\Zend_Currency::NO_SYMBOL], false);
                    $amount = str_replace(',', '', $amount);
                    $item['amount'] = $this->pricingHelper->currency($amount, true, false);
                } else {
                    $item[$this->getData('name')] =  $item[$this->getData('name')];
                }
            }
        }
        return $dataSource;
    }
}
