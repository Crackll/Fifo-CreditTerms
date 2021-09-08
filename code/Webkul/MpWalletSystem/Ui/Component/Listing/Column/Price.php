<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Ui\Component\Listing\Column;

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
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * Constructor
     *
     * @param ContextInterface       $context
     * @param UiComponentFactory     $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param array                  $components
     * @param array                  $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode = $this->helper->getBaseCurrencyCode();
                $item[$this->getData('name')] = $this->priceFormatter->format(
                    $item[$this->getData('name')],
                    false,
                    null,
                    null,
                    $currencyCode
                );
                if (isset($item['transaction_note'])) {
                    $item['transaction_note'] = strip_tags($item['transaction_note']);
                }
            }
        }

        return $dataSource;
    }
}
