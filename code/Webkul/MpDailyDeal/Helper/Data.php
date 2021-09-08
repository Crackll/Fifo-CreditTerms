<?php
namespace Webkul\MpDailyDeal\Helper;

/**
 * Webkul_MpDailyDeal data helper
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProTypeModel;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $fullPageCache;

    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $localeDate;

    /**
     * @var PricingHelper
     */
    public $pricingHelper;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @var LinkRepositoryInterface
     */
    public $linkRepositoryInterface;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    public $productFactory;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    public $response;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * @var ConfigurableProTypeModel
     */
    public $configurableProTypeModel;

    /**
     * @var ProductCollectionFactory
     */
    public $productCollection;

    /**
     * \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
    /**
     * @var GroupedProTypeModel
     */
    public $_groupedProTypeModel;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param TimezoneInterface                     $localeDate
     * @param ProductRepositoryInterface            $productRepository
     * @param LinkRepositoryInterface               $linkRepositoryInterface
     * @param PricingHelper                         $pricingHelper
     * @param LinkRepositoryInterface               $linkRepositoryInterface
     * @param ProductFactory                        $productFactory
     * @param \Magento\Framework\App\Response\Http  $response
     * @param ConfigurableProTypeModel              $configurableProTypeModel
     * @param \Magento\Framework\App\Request\Http   $request
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        LinkRepositoryInterface $linkRepositoryInterface,
        PricingHelper $pricingHelper,
        ProductFactory $productFactory,
        \Magento\Framework\App\Response\Http $response,
        ConfigurableProTypeModel $configurableProTypeModel,
        \Magento\Framework\App\Request\Http $request,
        ProductCollectionFactory $productCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyObject,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        GroupedProTypeModel $groupedProTypeModel,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceModel
    ) {
        $this->_groupedProTypeModel = $groupedProTypeModel;
        $this->localeDate = $localeDate;
        $this->priceCurrencyObject = $priceCurrencyObject;
        $this->pricingHelper = $pricingHelper;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->linkRepositoryInterface = $linkRepositoryInterface;
        $this->response = $response;
        $this->configurableProTypeModel = $configurableProTypeModel;
        $this->request = $request;
        $this->productCollection = $productCollection;
        $this->moduleManager = $context->getModuleManager();
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->_stockFilter = $stockFilter;
        $this->productResourceModel = $productResourceModel;
        $this->today = $this->converToTz($this->localeDate->date()->format('Y-m-d H:i:s'));
        parent::__construct($context);
    }

    /**
     * @param object $product
     * @return array|flase []
     */
    public function getProductDealDetail($product)
    {
        $tempproduct = $this->productRepository->getById($product->getEntityId());
        $tempproduct->setStoreId($this->storeManager->getStore()->getId());
        $proUrl = $tempproduct->getProductUrl();
        $product = $this->productRepository->getById($product->getEntityId());
        $product->setStoreId(0);
        $collection = $this->productCollection->create()->addAttributeToSelect('*')
                                            ->addAttributeToFilter('entity_id', ['in' => $product->getEntityId()])
                                            ->addAttributeToFilter('type_id', ['nin'=> ['grouped','configurable']]);
                                            
        $this->_stockFilter->addInStockFilterToCollection($collection);
        $stockcollection=$collection;
        $dealStatus = $product->getDealStatus();
        $content = false;
        $modEnable = $this->scopeConfig->getValue('mpdailydeals/general/enable');
        if ($modEnable && $stockcollection->getSize()) {

            $content = ['deal_status' => $dealStatus];
            $today = $this->localeDate->date()->format('Y-m-d H:i:s');
            $dealFromDateTime = $this->localeDate->date(
                strtotime($product->getDealFromDate())
            )->format('Y-m-d H:i:s');
            $dealToDateTime = $this->localeDate->date(
                strtotime($product->getDealToDate())
            )->format('Y-m-d H:i:s');
            $difference = strtotime($dealToDateTime) - strtotime($today);
            $specialPrice = $product->getSpecialPrice();
            $price = $product->getPrice();
            if ($modEnable && $difference > 0 && $dealFromDateTime < $today && $product->getDealStatus()) {

                $content['update_url'] = $this->_urlBuilder->getUrl('mpdailydeal/index/updatedealinfo');
                if ($product->getTypeId() == 'bundle') {
                    $content['stoptime'] = $dealToDateTime;
                } else {
                    $content['stoptime'] = $product->getSpecialToDate();
                }
                $content['diff_timestamp'] = $difference;
                $content['discount-percent'] = $product->getDealDiscountPercentage();
                $content['special-price'] = $specialPrice;
                $content['deal-from-date'] = $dealFromDateTime;
                $content['deal-to-date'] = $dealToDateTime;
                $content['deal_id'] = $product->getEntityId();
                if ($product->getTypeId() != 'bundle') {
                    $content['saved-amount'] = $this->pricingHelper
                                                ->currency($price - $specialPrice, true, false);
                    $content['saved-amount-raw'] = $price - $specialPrice;
                }

                $this->setPriceAsDeal($product);
            } elseif ($modEnable && ($dealToDateTime <= $today)) {
                $token = 0;
                
                if ($product->getDealStatus()) {
                    $token = 1;
                }
                if ($token) {
                    $productResourceModel = $this->productResourceModel->create();
                    $productResourceModel->load($product, $product->getEntityId());
                    $product->setSpecialPrice('');
                    $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                    $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                    $productResourceModel->saveAttribute($product, 'special_price');
                    $productResourceModel->saveAttribute($product, 'special_from_date');
                    $productResourceModel->saveAttribute($product, 'special_to_date');
                    $product->setDealStatus(0);
                    $product->getResource()->saveAttribute($product, 'deal_to_date');
                    $product->getResource()->saveAttribute($product, 'deal_from_date');
                    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                    $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                    $product->save();
                    $config = $this->configurableProTypeModel
                                ->getParentIdsByChild($product->getEntityId());
                    if ($this->request->getFullActionName() == 'catalog_product_view' && !isset($config[0])) {
                        if (strpos($proUrl, '?___store=admin')===false) {
                            $this->cleanByTags($product->getId(), 'P');
                            $this->response->setRedirect($proUrl);
                        }
                    }
                    $this->cleanByTags($product->getId(), 'P');
                    $content = false;
                }
                $content = false;
            }
        }
        return $content;
    }

    /**
     * setPriceAsDeal
     * @param ProductRepositoryInterface $product
     * @return void
     */
    public function setPriceAsDeal($product)
    {
        $tempproduct = $this->productRepository->getById($product->getEntityId());
        $tempproduct->setStoreId($this->storeManager->getStore()->getId());
        $proUrl = $tempproduct->getProductUrl();

        $proType = $product->getTypeId();
        $dealSupport = true;
        if ($proType == 'bundle' && !$product->getPriceType()) {
            $dealSupport = true;
        }
        if ($product->getDealDiscountType() == 'percent') {
            if ($dealSupport) {
                $price = $product->getPrice() * ($product->getDealValue()/100);
                if ($proType == 'bundle' && !$product->getPriceType()) {
                    $price = $product->getDealValue();
                }
            } else {
                $price = $product->getDealValue();
                $product->setPrice(null);
            }
            $discount = $product->getDealValue();
        } else {
            $price = $product->getDealValue();
            if ($product->getPrice()) {
                $discount = ($product->getDealValue()/$product->getPrice())*100;
            } else {
                $discount = 0;
            }
        }
        $token = 0;
        if (abs($product->getSpecialPrice() - $price) < 0.00001) {
            $token = 1;
        }
        $product->setDealDiscountPercentage(round(100-$discount));
        if ($proType == 'downloadable') {
            $links = $this->linkRepositoryInterface->getLinksByProduct($product);
            $product->setDownloadableLinks($links);
        }
        if ($dealSupport) {
            if (!$token) {
                $product->setDealStatus(1);
                
                $productResourceModel = $this->productResourceModel->create();
                $productResourceModel->load($product, $product->getEntityId());
                $product->setSpecialPrice($price);
                $product->setSpecialFromDate(date('Y-m-d', strtotime($product->getDealFromDate())));
                $product->setSpecialToDate(date('Y-m-d', strtotime($product->getDealToDate())));
                $productResourceModel->saveAttribute($product, 'special_price');
                $productResourceModel->saveAttribute($product, 'special_from_date');
                $productResourceModel->saveAttribute($product, 'special_to_date');

                $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                $product->save();

                $config = $this->configurableProTypeModel->getParentIdsByChild($product->getEntityId());
                try {
                    if ($this->request->getFullActionName() == 'catalog_product_view'
                        && !isset($config[0])) {
                        
                        if (strpos($proUrl, '?___store=admin')===false) {
                            $this->cleanByTags($product->getId(), 'P');
                            $this->response->setRedirect($proUrl);
                        }
                    }
                } catch (\Exception $e) {
                }
                $this->cleanByTags($product->getId(), 'P');
                $product->unsetDealStatus();
            }
        }
    }

    /**
     * @param object $sellerId
     * @return array
     */
    public function getSellerProductsIds($sellerId)
    {
        $proIds = false;
        $sellerColl = $this->productFactory->create()->getCollection()
                                ->addFieldToFilter('seller_id', ['eq' =>$sellerId]);
        if ($sellerColl->getSize()) {
            $proIds[] = 0;
            foreach ($sellerColl as $product) {
                $proIds[] = $product->getMageproductId();
            }
        }
        $proIds = $this->productCollection->create()
                    ->addFieldToFilter('type_id', ['in'=>['simple', 'virtual', 'downloadable', 'bundle']])
                    ->addFieldToFilter('entity_id', ['in'=>$proIds])
                    ->getColumnValues('entity_id');
        return $proIds;
    }

    /**
     * get deal enable value
     *
     * @return boolean
     */
    public function isDealEnable()
    {
        return $this->scopeConfig->getValue('mpdailydeals/general/enable');
    }

    /**
     * get all Deal product Ids
     *
     * @return Array
     */
    public function getDealProductIds()
    {
        $ids = [];
        if ($this->isDealEnable()) {
            $collection = $this->productCollection->create();
            $collection->addAttributeToFilter('deal_status', 1);
            if ($this->moduleManager->isEnabled('Webkul_MpHyperLocal')) {
                $helper = $this->objectManager->get(\Webkul\MpHyperLocal\Helper\Data::class);
                $sellerIds = $helper->getNearestSellers();
                $allowedProList = $helper->getNearestProducts($sellerIds);
                $collection->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
            }
            $collection->addAttributeToFilter('deal_from_date', ['lt'=>$this->today]);
            $collection->addAttributeToFilter('deal_to_date', ['gt'=>$this->today]);
            $ids = $collection->getColumnValues('entity_id');
            $ids = $this->getConfigurableProIds($ids, $collection);
            $ids = $this->getGroupedProIds($ids, $collection);
        }
        return $ids;
    }
    
    /**
     * Get Configurable Product Ids
     *
     * @param array $ids
     * @param \Magento\Catalog\Model\Product\Collection $collection
     * @return array
     */
    public function getConfigurableProIds($ids, $collection)
    {
        $associatedProdIds =  $collection->addAttributeToFilter('visibility', ['eq' => 1])
                            ->getColumnValues('entity_id');
        foreach ($associatedProdIds as $id) {
            $details = $this->getConfigProId($id);
            $ids = [...$ids, ...$details];
        }
        return $ids;
    }
    public function getGroupedProIds($ids, $collection)
    {
        $associatedProdIds =  $collection->addAttributeToFilter('visibility', ['eq' => 1])
                                ->getColumnValues('entity_id');
        foreach ($associatedProdIds as $id) {
            $details = $this->_groupedProTypeModel->getParentIdsByChild($id);
            if (isset($details[0])) {
                array_push($ids, $details[0]);
            }
        }
        return $ids;
    }

    /**
     * Convert amount based on currency
     *
     * @param integer $amount
     * @param integer $store
     * @param string $currency
     * @return int
     */
    public function getConvertedAmount($amount = 0, $store = null, $currency = null)
    {
        $currency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        if ($store == null) {
            $store = $this->storeManager->getStore()->getStoreId();
        }
        $rate = $this->priceCurrencyObject
                ->convert($amount, $includeContainer = true, $precision = 2, $store, $currency);
        return $rate;
    }

    /**
     * Load Product
     *
     * @param int $id
     * @return \Magento\Catalog\Model\Product
     */
    public function loadProductById($id)
    {
        return $this->productRepository->getById($id);
    }

    /**
     * Ger Configurable Product Id
     *
     * @param int $id
     * @return int
     */
    private function getConfigProId($id)
    {
        return $this->configurableProTypeModel->getParentIdsByChild($id);
    }

    private function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\PageCache\Model\Cache\Type::class
            );
        }
        return $this->fullPageCache;
    }
    
    public function cleanByTags($id = null, $tagtype = null)
    {
        if ($tagtype=='P') {
            $productId = $id;
            $tags = ['CAT_P_'.$productId];
        } elseif ($tagtype=='C') {
            $catId = $id;
            $tags = ['CAT_C'.$catId];
        } else {
            $tags = ['CAT_P_'.$id];
        }

        $this->getCache()->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
    }

    public function getMaxDiscount($allDeals)
    {
        $minVal = 99999999;
        $result = [];
        foreach ($allDeals as $deal) {
            if ($deal['special-price']<=$minVal) {
                $minVal = $deal['special-price'];
                $result = $deal;
            }
        }
        return $result;
    }

    public function converToTz($dateTime = "", $fromTz = '', $toTz = '')
    {
        if (!$fromTz) {
            $fromTz = $this->localeDate->getConfigTimezone();
        }
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone('UTC'));
        $dateTime = $date->format('Y-m-d H:i:s');
        return $dateTime;
    }
}
