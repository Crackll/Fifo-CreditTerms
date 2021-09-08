<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SampleProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\SampleProduct\Model\SampleProductFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as InitializationHelper;
use Magento\Framework\App\ObjectManager;

class CatalogProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Webkul\SampleProduct\Model\SampleProductFactory
     */
    protected $_sampleProductFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var InitializationHelper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param SampleProductFactory $sampleProductFactory
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @param InitializationHelper $initializationHelper
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    protected $_productQtyFactory;

    public function __construct(
        RequestInterface $request,
        SampleProductFactory $sampleProductFactory,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository = null,
        InitializationHelper $initializationHelper,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        $this->_request = $request;
        $this->_sampleProductFactory = $sampleProductFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository ?:ObjectManager::getInstance()
            ->get(ProductRepositoryInterface::class);
        $this->initializationHelper = $initializationHelper;
        $this->productTypeManager = $productTypeManager;
        $this->messageManager = $messageManager;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
    }

    /**
     * Product save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->_request->getParam('type') == 'sample') {
                return true;
            }
            $product = $observer->getProduct();
            $requestData = $this->_request->getParams();

            // set sample request data
            $sampleProductId = '';
            $collection = $this->_sampleProductFactory->create()
                ->getCollection()
                ->addFieldToFilter('product_id', $product->getId());
            foreach ($collection as $value) {
                $sampleProductId = $value->getSampleProductId();
            }

           
         
            if (!$sampleProductId && !isset($requestData['sample']['status'])) {
                return true;
            }
          
            if (!$sampleProductId && isset($requestData['sample']['status'])) {
            
                if(!$requestData['sample']['status']){
                    return true;
                }
            }
            $sampleRequestData = $this->createSampleProductRequestData(
                $sampleProductId,
                $requestData
            );
            $this->saveSampleProduct(
                $product->getId(),
                $requestData['sample']['status'],
                $sampleRequestData
            );
            if (!empty($requestData['use_default'])) {
                $requestData['use_default']['name'] = 1;
                $this->_request->setPostValue('use_default', $requestData['use_default']);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    public function createSampleProductRequestData($sampleProductId, $requestData)
    {
        $sampleRequestData = [];
        $sampleRequestData['id'] = $sampleProductId;
        $sampleRequestData['store'] = $this->_request->getParam('store', 0);
        $sampleRequestData['set'] = (int) $this->_request->getParam('set');
        $sampleRequestData['type'] = 'sample';
        $sampleRequestData['product'] = [];
        if (!empty($requestData['use_default'])) {
            $requestData['use_default']['name'] = 0;
            $this->_request->setPostValue('use_default', $requestData['use_default']);
        }
        if ($requestData['sample']['status']) {
            $sampleRequestData['product']['status'] = Product\Attribute\Source\Status::STATUS_ENABLED;
        } else {
            $sampleRequestData['product']['status'] = Product\Attribute\Source\Status::STATUS_DISABLED;
        }

        if ($sampleProductId && !$requestData['sample']['status']) {
            try {
                $storeId = $this->_request->getParam('store', 0);
                $product = $this->productRepository->getById($sampleProductId, true, $storeId);
                $sampleRequestData['product']['product'] = $product['name'];
                $sampleRequestData['product']['price'] = $product['price'];
                $sampleRequestData['product']['sku'] = $product['sku'];
                $sampleRequestData['product']['description'] = $product['description'];
                $sampleRequestData['product']['quantity_and_stock_status'] = $product['quantity_and_stock_status'];
                $sampleRequestData['product']['visibility'] = $product['visibility'];
                $sampleRequestData['product']['product_has_weight'] = $product['product_has_weight'];
                $sampleRequestData['product']['weight'] = $product['weight'];
                $sampleRequestData['product']['type_id'] = 'sample';
                return $sampleRequestData;
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        if (empty($requestData['sample']['title'])) {
            $sampleRequestData['product']['name'] = $requestData['product']['name'].' - Sample';
        } else {
            $sampleRequestData['product']['name'] = $requestData['sample']['title'];
        }
        if (empty($requestData['sample']['price'])) {
            $sampleRequestData['product']['price'] = '0.0000';
        } else {
            $sampleRequestData['product']['price'] = $requestData['sample']['price'];
        }

        $sampleRequestData['product']['sku'] = $requestData['product']['sku'].'-sample';
        $sampleRequestData['product']['description'] = $requestData['product']['description'];
        $sampleRequestData['product']['quantity_and_stock_status'] =
        $requestData['product']['quantity_and_stock_status'];
        /*
        * Manage sample product Stock data
        */
        $sampleRequestData = $this->manageSampleProductStock($sampleRequestData, $requestData);
        $sampleRequestData['product']['visibility'] = Product\Visibility::VISIBILITY_NOT_VISIBLE;
        $sampleRequestData['product']['product_has_weight'] = $requestData['product']['product_has_weight'];
        $sampleRequestData['product']['weight'] = $requestData['product']['weight'];
        $sampleRequestData['product']['website_ids'] = $requestData['product']['website_ids'];
        $sampleRequestData['product']['type_id'] = 'sample';
        if (!empty($requestData['salable_quantity'])) {
            $sampleRequestData['salable_quantity'] = $requestData['salable_quantity'];
            foreach ($sampleRequestData['salable_quantity'] as $key => $value) {
                $sampleRequestData['salable_quantity'][$key]['qty'] = $requestData['sample']['qty'];
            }
        }

        return $sampleRequestData;
    }

    public function saveSampleProduct($productId, $sampleStatus, $sampleRequestData)
    {
        if ($sampleRequestData) {
            try {
                $productData = $sampleRequestData['product'];
                $sampleProduct = $this->initializationHelper->initializeFromData(
                    $this->sampleProductBuild($sampleRequestData),
                    $productData
                );
                $this->productTypeManager->processProduct($sampleProduct);
                $sampleProduct->setTypeId('sample');
                if (isset($sampleRequestData['product'][$sampleProduct->getIdFieldName()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The sample product was unable to be saved. Please try again.')
                    );
                }

                $originalSku = $sampleProduct->getSku();
                $canSaveCustomOptions = $sampleProduct->getCanSaveCustomOptions();
                $sampleProduct->save();
                $sampleProductId = $sampleProduct->getEntityId();

                $sampleId = '';
                $collection = $this->_sampleProductFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('sample_product_id', $sampleProductId);
                foreach ($collection as $value) {
                    $sampleId = $value->getId();
                }
                // save sample data to custom table
                $sample = $this->_sampleProductFactory->create();
                if ($sampleId) {
                    $sample->setId($sampleId);
                }
                $sample->setProductId($productId);
                $sample->setSampleProductId($sampleProductId);
                $sample->setStatus($sampleStatus);
                $sample->save();

                $extendedData = $sampleRequestData;
                $extendedData['can_save_custom_options'] = $canSaveCustomOptions;
                $this->copySampleDataToStores($extendedData, $sampleProductId);
                $this->messageManager->addSuccessMessage(__('You saved the sample for this product.'));
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
    }

    public function sampleProductBuild($sampleRequestData): ProductInterface
    {
        $productId = (int) $sampleRequestData['id'];
        $storeId = $sampleRequestData['store'];
        $attributeSetId = (int) $sampleRequestData['set'];
        $typeId = $sampleRequestData['type'];

        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId, true, $storeId);
                if ($attributeSetId) {
                    $product->setAttributeSetId($attributeSetId);
                }
            } catch (\Exception $e) {
                $product = $this->createEmptySampleProduct(
                    \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE,
                    $attributeSetId,
                    $storeId
                );
                $this->logger->critical($e);
            }
        } else {
            $product = $this->createEmptySampleProduct($typeId, $attributeSetId, $storeId);
        }

        return $product;
    }

    /**
     * Create a product with the given properties
     *
     * @param int $typeId
     * @param int $attributeSetId
     * @param int $storeId
     * @return \Magento\Catalog\Model\Product
     */
    private function createEmptySampleProduct($typeId, $attributeSetId, $storeId): Product
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        $product->setData('_edit_mode', true);

        if ($typeId !== null) {
            $product->setTypeId($typeId);
        }

        if ($storeId !== null) {
            $product->setStoreId($storeId);
        }

        if ($attributeSetId) {
            $product->setAttributeSetId($attributeSetId);
        }

        return $product;
    }

    /**
     * Do copying data to stores
     *
     * @param array $sampleData
     * @param int $sampleProductId
     *
     * @return void
     */
    protected function copySampleDataToStores($sampleData, $sampleProductId)
    {
        if (!empty($sampleData['product']['copy_to_stores'])) {
            foreach ($sampleData['product']['copy_to_stores'] as $websiteId => $group) {
                if (isset($sampleData['product']['website_ids'][$websiteId])
                    && (bool)$sampleData['product']['website_ids'][$websiteId]) {
                    foreach ($group as $store) {
                        $this->copySampleDataToStore($sampleData, $sampleProductId, $store);
                    }
                }
            }
        }
    }

    /**
     * Do copying sample data to stores
     *
     * If the 'copy_from' field is not specified in the input data,
     * the store fallback mechanism will automatically take the admin store's default value.
     *
     * @param array $sampleData
     * @param int $sampleProductId
     * @param array $store
     */
    private function copySampleDataToStore($sampleData, $sampleProductId, $store)
    {
        if (isset($store['copy_from'])) {
            $copyFrom = $store['copy_from'];
            $copyTo = (isset($store['copy_to'])) ? $store['copy_to'] : 0;
            if ($copyTo) {
                $this->_objectManager->create(\Magento\Catalog\Model\Product::class)
                    ->setStoreId($copyFrom)
                    ->load($sampleProductId)
                    ->setStoreId($copyTo)
                    ->setCanSaveCustomOptions($sampleData['can_save_custom_options'])
                    ->setCopyFromView(true)
                    ->save();
            }
        }
    }

    /**
     * @param array $requestProductData
     *
     * @return array
     */
    private function manageSampleProductStock($sampleRequestData, $requestProductData)
    {
        if ($requestProductData && !empty($requestProductData['sample']['qty'])) {
            $sampleRequestData['product']['stock_data']['qty'] = $requestProductData['sample']['qty'];
            $stockData = isset($requestProductData['stock_data']) ?
            $requestProductData['stock_data'] : [];
            if (isset($requestData['sample']['qty']) && (double) $requestProductData['sample']['qty'] > 99999999.9999) {
                $sampleRequestData['product']['stock_data']['qty'] = 99999999.9999;
            }
            if (isset($stockData['min_qty']) && (int) $stockData['min_qty'] < 0) {
                $sampleRequestData['product']['stock_data']['min_qty'] = 0;
            }
            if (!isset($stockData['use_config_manage_stock'])) {
                $sampleRequestData['product']['stock_data']['use_config_manage_stock'] = 0;
            } else {
                if ($stockData['use_config_manage_stock'] == 1 && !isset($stockData['manage_stock'])) {
                    $sampleRequestData['product']['stock_data']['manage_stock'] = $this->stockConfiguration
                    ->getManageStock();
                }
            }
        }
        return $sampleRequestData;
    }
}
