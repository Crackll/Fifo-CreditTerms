<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpMSI
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpMSI\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;
use Webkul\Marketplace\Helper\Data as HelperData;

class Inventory extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\InventoryApi\Api\SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @var \Magento\InventoryApi\Api\StockRepositoryInterface
     */
    protected $stockRepository;

    /**
     * @var ServiceOutputProcessor
     */
    protected $serviceOutputProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku
     */
    protected $sourceDataBySkul;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var  \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    protected $singleSourceMode;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var \Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty
     */
    protected $getSourcesByOrderIdSkuAndQty;

    /**
     * @var Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var HelperData
     */
    protected $helper;
    
    /**
     * __constructor
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Product $product
     * @param \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository
     * @param \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository
     * @param \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\InventoryCatalog\Model\IsSingleSourceMode $singleSourceMode
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param \Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty
     * $getSourcesByOrderIdSkuAndQty
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        Product $product,
        HelperData $helper,
        \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository,
        \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository,
        \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\InventoryCatalog\Model\IsSingleSourceMode $singleSourceMode,
        SourceItemRepositoryInterface $sourceItemRepository,
        \Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty $getSourcesByOrderIdSkuAndQty,
        \Magento\Sales\Model\OrderFactory $order,
        array $data = []
    ) {
        $this->_product = $product;
        $this->helper = $helper;
        $this->sourceRepository = $sourceRepository;
        $this->stockRepository = $stockRepository;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceDataBySku = $sourceDataBySku;
        $this->_productRepository = $productRepository;
        $this->jsonHelper = $jsonHelper;
        $this->singleSourceMode = $singleSourceMode;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->getSourcesByOrderIdSkuAndQty = $getSourcesByOrderIdSkuAndQty;
        $this->order = $order;
        parent::__construct($context, $data);
    }

    /**
     * get current product
     *
     * @return Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $id = $this->getRequest()->getParam("id");
        try {
            return $this->_productRepository->getById($id);
        } catch (\Exception $e) {
            return $this->_product;
        }
    }

    /**
     * @return get sources by sku
     *
     */
    public function getSourcesBySku()
    {
        $stocks = [];
        if ($this->getProduct()->getId()) {
            $sku = $this->getProduct()->getSku();
            $stocks = $this->sourceDataBySku->execute($sku);
        }
        return $this->jsonHelper->jsonEncode($stocks);
    }

    /**
     * @return get Product Type
     */
    
    public function getProductType()
    {
        $product = $this->getProduct();
        if ($product->getId() != '') {
            return $product->getTypeId();
        } else {
            return $this->getRequest()->getParam('type');
        }
    }

    /**
     * get all sources
     *
     * @return []
     */
    public function getSources()
    {
        try {
            $source = $this->sourceRepository->getList($this->searchCriteriaBuilder->create());
            $sourceItems = $this->serviceOutputProcessor->convertValue(
                $source,
                \Magento\InventoryApi\Api\Data\SourceSearchResultsInterface::class
            );
            return $this->jsonHelper->jsonEncode($sourceItems);
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Inventory Block execute : ".$e->getMessage()
            );
        }
    }

    /**
     * get sorces for shipment
     *
     * @return []
     */
    public function getSourcesForOrder()
    {

        $order = $this->getCurrentOrder();
        $sourceCodes = [];
        foreach ($order->getAllItems() as $orderItem) {
            $item = $orderItem->isDummy(true) ? $orderItem->getParentItem() : $orderItem;
            $qty = $item->getSimpleQtyToShip();
            if ($item->getIsQtyDecimal()) {
                $qty = (double)$qty;
            } else {
                $qty = (int)$qty;
            }
            $sku = $orderItem->getSku();
            $sources = $this->getSourcesByOrderIdSkuAndQty->execute($order->getId(), $sku, $qty);
            foreach ($sources as $source) {
                $sourceCodes[$source['sourceCode']] = $source['sourceName'];
            }
        }

        return $sourceCodes;
    }

    /**
     * get current order
     *
     * @return Order|boolean
     */
    public function getCurrentOrder()
    {
        $orderId = $this->getRequest()->getParam("id");
        if ($orderId) {
            return $this->order->create()->load($orderId);
        }

        return false;
    }

    /**
     * get admin set default notify quantity
     *
     * @return int
     */
    public function getConfigNotifyQuantity()
    {
        return 1;
    }

    /**
     * if multi source mode enabled
     *
     * @return boolean
     */
    public function isSingleStoreMode()
    {
        return $this->singleSourceMode->execute();
    }
}
