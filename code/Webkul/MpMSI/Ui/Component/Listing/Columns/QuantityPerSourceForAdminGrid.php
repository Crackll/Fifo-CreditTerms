<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMSI
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMSI\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Framework\UrlInterface;

class QuantityPerSourceForAdminGrid extends Column
{
    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var HelperData
     */
    public $helperData;
    
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Webkul\MpMSI\Helper\Data
     */
    protected $msiHelper;

   /**
    * Constructor
    *
    * @param ContextInterface $context
    * @param UiComponentFactory $uiComponentFactory
    * @param CollectionFactory $collectionFactory
    * @param HelperData $helperData
    * @param UrlInterface $urlBuilder
    * @param \Webkul\MpMSI\Helper\Data $msiHelper
    * @param array $components
    * @param array $data
    */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        UrlInterface $urlBuilder,
        \Webkul\MpMSI\Helper\Data $msiHelper,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
        $this->msiHelper = $msiHelper;
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
            $sellerId = $this->helperData->getCustomerId();
            foreach ($dataSource['data']['items'] as &$item) {
                $product = $this->msiHelper->getProductById($item['mageproduct_id']);
                $item['sku'] = $product->getSku();
                if (isset($item['sku'])) {
                    $stocks = [];
                    $sku = $item['sku'];
                    $stocks = $this->msiHelper->getSourceQtyBySku($sku);
                    $strockHtml = "<ul>";
                    foreach ($stocks as $stock) {
                        $strockHtml .= "<li>{$stock['name']} : {$stock['quantity']}</li>";
                    }
                    $strockHtml .= "</ul>";
                    $item[$fieldName] = $strockHtml;
                }
            }
        }

        return $dataSource;
    }
}
