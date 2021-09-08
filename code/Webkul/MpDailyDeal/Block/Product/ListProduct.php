<?php
namespace Webkul\MpDailyDeal\Block\Product;

/**
 * Webkul_DailyDeals ListProduct collection block.
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
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;
    
    /**
     * @param Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver     $layerResolver
     * @param CategoryRepositoryInterface               $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data        $urlHelper
     * @param CollectionFactory                         $productFactory
     * @param ReportsProducts\CollectionFactory         $reportproductsFactory,
     * @param SalesReportFactory\CollectionFactory      $salesReportFactory
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        CollectionFactory $productFactory,
        ReportsProducts\CollectionFactory $reportproductsFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SalesReportFactory\CollectionFactory $salesReportFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Magento\Bundle\Model\Product\Type $bundleType
    ) {
        $this->grouped = $grouped;
        $this->_productFactory = $productFactory;
        $this->_reportproductsFactory = $reportproductsFactory;
        $this->_salesReportFactory = $salesReportFactory;
        $this->_moduleManager = $moduleManager;
        $this->_objectManager= $objectManager;
        $this->configurable = $configurable;
        $this->bundleType = $bundleType;
        
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper
        );
        $this->_today = $this->converToTz($this->_localeDate->date()->format('Y-m-d H:i:s'));
    }

    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function _getProductCollection()
    {
        if (!$this->_productCollection) {
            $paramData = $this->getRequest()->getParams();
            $productname = $this->getRequest()->getParam('name');
            $simpledealIds = $this->_productFactory
                ->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('deal_status', 1)
                ->addFieldToFilter('visibility', ['neq' => 1])
                ->addFieldToFilter('type_id', ['in' => ['simple','downloadable','virtual']])
                ->addAttributeToFilter(
                    'deal_from_date',
                    ['lt'=>$this->_today]
                )->addAttributeToFilter(
                    'deal_to_date',
                    ['gt'=>$this->_today]
                )->getColumnValues('entity_id');
            $notvisibleIds = $this->_productFactory
                ->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('deal_status', 1)
                ->addFieldToFilter('visibility', ['eq' => 1])
                ->addFieldToFilter('type_id', ['in' => ['simple','downloadable','virtual']])
                ->addAttributeToFilter(
                    'deal_from_date',
                    ['lt'=>$this->_today]
                )->addAttributeToFilter(
                    'deal_to_date',
                    ['gt'=>$this->_today]
                )->getColumnValues('entity_id');
            $configurableIds = [0];
            $groupnotvisible=[0];
            $bundlenotvisible=[0];
            $groupproIds= [0];
            $bundleproIds= [0];
            foreach ($simpledealIds as $notvisiblesimpleIds) {
                $parentIds = $this->configurable->getParentIdsByChild($notvisiblesimpleIds);
                $configurableIds = [...$configurableIds, ...$parentIds];
                $groupparentIds = $this->grouped->getParentIdsByChild($notvisiblesimpleIds);
                $groupproIds= [...$groupproIds,...$simpledealIds,...$groupparentIds];
                $bundleparentIds = $this->bundleType->getParentIdsByChild($notvisiblesimpleIds);
                $bundleproIds= [...$bundleproIds,...$simpledealIds,...$bundleparentIds];
            }

            foreach ($notvisibleIds as $notvisibleId) {
                $parentIds = $this->configurable->getParentIdsByChild($notvisibleId);
                $groupparentnotvisible = $this->grouped->getParentIdsByChild($notvisibleId);
                $groupnotvisible=[...$groupnotvisible,...$groupparentnotvisible];
                $bundleparentnotvisible = $this->bundleType->getParentIdsByChild($notvisibleId);
                $bundlenotvisible=[...$bundlenotvisible,...$bundleparentnotvisible];
                $configurableIds = [...$configurableIds,...$parentIds];
            }
            $bundleProIds1 = $this->_productFactory
                ->create()
                ->addAttributeToSelect('type_id')
                ->addAttributeToSelect('price_type')
                ->addFieldToFilter('type_id', 'bundle')
                ->addFieldToFilter('price_type', 0)
                ->addFieldToFilter('entity_id', ['in'=>[...$bundleproIds, ...$bundlenotvisible]])
                ->getColumnValues('entity_id');
            $bundleProIds2 = $this->_productFactory
                ->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('deal_status', 1)
                ->addFieldToFilter('visibility', ['neq' => 1])
                ->addFieldToFilter('type_id', 'bundle')
                ->addAttributeToFilter(
                    'deal_from_date',
                    ['lt'=>$this->_today]
                )->addAttributeToFilter(
                    'deal_to_date',
                    ['gt'=>$this->_today]
                )->getColumnValues('entity_id');
            $bundleProIds = [...$bundleProIds1, ...$bundleProIds2];
            $allProductIds = [
                ...$simpledealIds,
                ...$configurableIds,
                ...$groupnotvisible,
                ...$groupproIds,
                ...$bundleProIds
            ];
            $collection = $this->_productFactory
                            ->create()
                            ->addAttributeToSelect('*')
                            ->addFieldToFilter('entity_id', ['in'=>$allProductIds]);
                            
            $layer = $this->getLayer();

            $origCategory = null;
    
            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            $this->_productCollection = $collection;

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
            $toolbar = $this->getToolbarBlock();
            $this->configureProductToolbar($toolbar, $collection);
        }
        $this->_productCollection->getSize();

        return $this->_productCollection;
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
        if (isset($availableOrders['position'])) {
            unset($availableOrders['position']);
        }
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
        return 'name';
    }

    //sort according to deal percentage
    public function compareDealDiscountPercentage($a, $b)
    {
        return strcmp($a['deal_discount_percentage'], $b['deal_discount_percentage']);
    }

    /**
     * getTopDealsOfDay
     * @return CollectionFactory top 5 products on best deal
     */
    public function getTopDealsOfDay()
    {
        $productCollection = $this->_productCollection;
        $newProCollection = [];
       
        foreach ($productCollection as $product) {
            $type = $product->getTypeId();
            $productId = $product->getId();
            
            if ($type == 'configurable') {
                $_configChild = $product->getTypeInstance()->getUsedProductIds($product);
                $getChildId = [];
                foreach ($_configChild as $child) {
                    $getChildId[] = $child;
                }

                $maxVal = -99999999;
                $dealDetail = $this->_productFactory
                                        ->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('entity_id', ['in'=>$getChildId]);
                foreach ($dealDetail as $singleChild) {
                    if (isset($singleChild['deal_discount_percentage']) &&
                    $singleChild['deal_discount_percentage'] &&
                    $singleChild['deal_discount_percentage']>=$maxVal) {
                        $maxVal = $singleChild['deal_discount_percentage'];
                    }
                }
                    $product['deal_discount_percentage'] = $maxVal;
                    $newProCollection [] = $product;
            } elseif ($type == 'grouped') {
               
                $_groupChild = $product->getTypeInstance()->getAssociatedProducts($product);

                $getChildId = [];
                foreach ($_groupChild as $child) {
                    $getChildId[] = $child['entity_id'];
                }

                $maxVal = -99999999;
                $dealDetail = $this->_productFactory
                                        ->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('entity_id', ['in'=>$getChildId]);
                foreach ($dealDetail as $singleChild) {
                    if (isset($singleChild['deal_discount_percentage']) &&
                    $singleChild['deal_discount_percentage'] &&
                    $singleChild['deal_discount_percentage']>=$maxVal) {
                        $maxVal = $singleChild['deal_discount_percentage'];
                    }
                }
                   $product['deal_discount_percentage'] = $maxVal;
                   $newProCollection [] = $product;
            } else {
                $newProCollection [] = $product;
            }
            uasort($newProCollection, [$this, 'compareDealDiscountPercentage']);
        }
        return array_slice(array_reverse($newProCollection), 0, 5, true);
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
        return $this->_reportproductsFactory
                        ->create()
                        ->addAttributeToSelect('*')
                        ->addViewsCount()
                        ->setStoreId(0)
                        ->addStoreFilter(0)
                        ->addAttributeToFilter('deal_status', 1)
                        ->addFieldToFilter('status', 1)
                        ->addFieldToFilter('visibility', ['neq' => 1])
                        ->addAttributeToFilter(
                            'deal_from_date',
                            ['lt'=>$this->_today]
                        )->addAttributeToFilter(
                            'deal_to_date',
                            ['gt'=>$this->_today]
                        )->setPageSize(5);
    }

    /**
     * getDealViewsProduct
     * @return SalesReportFactory //best sold product
     */
    public function getTopSaleProduct()
    {
        $productIds = $this->_salesReportFactory->create()->setModel(\Magento\Catalog\Model\Product::class)
                                            ->addStoreFilter(0)->setPageSize(5)
                                            ->getColumnValues('product_id');

        if (empty($productIds)) {
            $productIds = [0];
        }
        return $this->_reportproductsFactory
                        ->create()
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('entity_id', ['in' => $productIds])
                        ->addFieldToFilter('visibility', ['neq' => 1])
                        ->addFieldToFilter('status', 1)
                        ->setPageSize(5);
    }
    public function converToTz($dateTime = "", $fromTz = '', $toTz = '')
    {
        if (!$fromTz) {
            $fromTz = $this->_localeDate->getConfigTimezone();
        }
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone('UTC'));
        $dateTime = $date->format('Y-m-d H:i:s');
        return $dateTime;
    }
}
