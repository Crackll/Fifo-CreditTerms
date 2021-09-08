<?php
namespace Webkul\MpDailyDeal\Block\Product;

/**
 * Webkul_DailyDeals Seller Deal Product collection block.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Reports\Model\ResourceModel\Product as ReportsProducts;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers as SalesReportFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Helper\Orders as MpOrderHelper;
use Magento\Catalog\Helper\Output as MagOutputHelper;
use Magento\Wishlist\Helper\Data as MagWishlistHelper;
use Magento\Catalog\Helper\Product\Compare as MagCompareHelper;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SellerDealProducts extends \Magento\Catalog\Block\Product\ListProduct
{
    protected $_productCollections = [];

    protected $_productlists = null;

    /**
     * @var \Webkul\MpDailyDeal\Helper\Data
     */
    protected $_helper;

    /**
     * Markeplace Products
     *
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    private $mpProductFactory;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var MpOrderHelper
     */
    protected $mpOrderHelper;

    /**
     * @var MagOutputHelper
     */
    protected $magOutputHelper;

    /**
     * @var MagWishlistHelper
     */
    protected $magWishlistHelper;

    /**
     * @var MagCompareHelper
     */
    protected $magCompareHelper;
    
    /**
     * @param \Magento\Catalog\Block\Product\Context    $context,
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
     * @param \Magento\Framework\Url\Helper\Data        $urlHelper,
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param CollectionFactory                         $productFactory,
     * @param \Magento\Catalog\Model\Layer\Resolver     $layerResolver,
     * @param CategoryRepositoryInterface               $categoryRepository,
     * @param ReportsProducts\CollectionFactory         $reportproductsFactory,
     * @param \Webkul\Marketplace\Model\ProductFactory  $mpProductFactory,
     * @param $data =[]
     */

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CollectionFactory $productFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        ReportsProducts\CollectionFactory $reportproductsFactory,
        \Webkul\MpDailyDeal\Helper\Data $helper,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        MpHelper $mpHelper,
        MpOrderHelper $mpOrderHelper,
        MagOutputHelper $magOutputHelper,
        MagWishlistHelper $magWishlistHelper,
        MagCompareHelper $magCompareHelper,
        TimezoneInterface $localeDate,
        array $data = []
    ) {
        $this->_reportproductsFactory = $reportproductsFactory;
        $this->localeDate = $localeDate;
        $this->mpProductFactory = $mpProductFactory;
        $this->mpHelper = $mpHelper;
        $this->mpOrderHelper = $mpOrderHelper;
        $this->magOutputHelper = $magOutputHelper;
        $this->magWishlistHelper = $magWishlistHelper;
        $this->magCompareHelper = $magCompareHelper;
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_today = $helper->converToTz($this->localeDate->date()->format('Y-m-d H:i:s'));
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * _getProductCollection
     * @return Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function _getProductCollection()
    {
        if ($this->_productlists == null) {
            $partner = $this->getProfileDetail();
            if ($partner) {
                try {
                    $sellerId = $partner->getSellerId();
                } catch (\Exception $e) {
                    $sellerId = 0;
                }
            } else {
                $sellerId = 0;
            }

            $layer = $this->getLayer();
            $querydata = $this->mpProductFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $sellerId]
                            )
                            ->addFieldToFilter(
                                'status',
                                ['eq' => 1]
                            )
                            ->addFieldToSelect('mageproduct_id')
                            ->setOrder('mageproduct_id')
                            ->getColumnValues('mageproduct_id');
           
            $collection = $this->getLayerProductCollection($layer);
            $collection->addAttributeToSelect('*');
            $productIds = array_intersect($querydata, $this->_helper->getDealProductIds());
            if (empty($productIds)) {
                $productIds = [0];
            }
            $toolbar = $this->getToolbarBlock();
            $this->configureProductToolbar($toolbar, $collection);
            $collection->addAttributeToFilter(
                'entity_id',
                ['in' => $productIds]
            );
            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $collection]
            );
            $this->_productlists = $collection;
        }
        $this->_productlists->getSize();

        return $this->_productlists;
    }

    public function getLayerProductCollection($layer)
    {
        if (isset($this->_productCollections[$layer->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$layer->getCurrentCategory()->getId()];
        } else {
            $collection = $layer->getCurrentCategory()->getProductCollection();
            $layer->prepareProductCollection($collection);
            $this->_productCollections[$layer->getCurrentCategory()->getId()] = $collection;
        }

        return $collection;
    }
    
    /**
     * Configures the Toolbar block for sorting related data.
     *
     * @param ProductList\Toolbar $toolbar
     * @param ProductCollection $collection
     * @return void
     */
    public function configureProductToolbar(Toolbar $toolbar, ProductCollection $collection)
    {
        $availableOrders = $this->getAvailableOrders();
        if ($availableOrders) {
            $toolbar->setAvailableOrders($availableOrders);
        }
        $sortBy = $this->getSortBy();
        if ($sortBy) {
            $toolbar->setDefaultOrder($sortBy);
        }
        $defaultDirection = $this->getDefaultDirection();
        if ($defaultDirection) {
            $toolbar->setDefaultDirection($defaultDirection);
        }
        $sortModes = $this->getModes();
        if ($sortModes) {
            $toolbar->setModes($sortModes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }

    public function getDefaultDirection()
    {
        return 'asc';
    }

    public function getSortBy()
    {
        return 'entity_id';
    }

    /**
     * Get Seller Profile Details
     *
     * @return \Webkul\Marketplace\Model\Seller | bool
     */
    public function getProfileDetail()
    {
        $helper = $this->mpHelper;
        return $helper->getProfileDetail(MpHelper::URL_TYPE_COLLECTION);
    }

    /**
     * getTopDealsOfDay
     * @return CollectionFactory top 5 products on best deal
     */

    public function getTopDealsOfDay()
    {
        $sellerProIds = $this->getSellerProductIds();
        return $this->_productCollectionFactory
                        ->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter(
                            'entity_id',
                            ['in' => $sellerProIds]
                        )->addAttributeToFilter('deal_status', 1)
                        ->setOrder(
                            'deal_discount_percentage',
                            'DESC'
                        )->setPageSize(5);
    }

    /**
     * getDealProductImage
     * @param Magento\Catalog\Model\Product $product
     * @return string product image url
     */
    public function getDealProductImage($product)
    {
        return $this->_imageHelper
                        ->init($product, 'category_page_grid')
                            ->constrainOnly(false)
                            ->keepAspectRatio(true)
                            ->keepFrame(false)
                            ->resize(400)
                            ->getUrl();
    }

    /**
     * getTopDealViewsProduct
     * @return ReportsProducts // top 5 viewed product
     */
    public function getTopDealViewsProduct()
    {
        $sellerProIds = $this->getSellerProductIds();
        return $this->_reportproductsFactory
                        ->create()
                        ->addAttributeToSelect('*')
                        ->addViewsCount()
                        ->setStoreId(0)
                        ->addStoreFilter(0)
                        ->addAttributeToFilter(
                            'entity_id',
                            ['in' => $sellerProIds]
                        )->addAttributeToFilter('deal_status', 1)
                        ->setPageSize(5);
    }

    /**
     * @return array of seller products ids
     */
    public function getSellerProductIds()
    {
        $partner = $this->getProfileDetail();
        $sellerProductIds = $this->mpProductFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'seller_id',
                                        ['eq' => $partner->getSellerId()]
                                    )->addFieldToFilter('status', ['eq' => 1])
                                    ->addFieldToSelect('mageproduct_id')
                                    ->setOrder('mageproduct_id');
        return $sellerProductIds->getData();
    }

    public function getDealHelper()
    {
        return $this->_helper;
    }

    public function geMpHelper()
    {
        return $this->mpHelper;
    }
    public function geMpOrderHelper()
    {
        return $this->mpOrderHelper;
    }

    public function getmagOutputHelper()
    {
        return $this->magOutputHelper;
    }

    public function getmagWishlistHelper()
    {
        return $this->magWishlistHelper;
    }

    public function getmagCompareHelper()
    {
        return $this->magCompareHelper;
    }
}
