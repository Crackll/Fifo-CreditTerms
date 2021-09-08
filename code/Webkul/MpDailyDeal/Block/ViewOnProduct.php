<?php
namespace Webkul\MpDailyDeal\Block;

/**
 * Webkul_MpDailyDeal View On Product Block.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;

class ViewOnProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CoreRegistry
     */
    protected $_coreRegistry;

    /**
     * @var \Webkul\MpDailyDeal\Helper\Data
     */
    protected $_helperData;

    /**
     * @var ConfigurableProTypeModel
     */
    protected $_configurableProTypeModel;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Magento\Catalog\Block\Product\Context   $context
     * @param Webkul\MpDailyDeal\Helper\Data          $helperData
     * @param ConfigurableProTypeModel                $configurableProTypeModel
     * @param ProductRepositoryInterface              $productRepository
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpDailyDeal\Helper\Data $helperData,
        ConfigurableProTypeModel $configurableProTypeModel,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
    
        $this->_coreRegistry = $context->getRegistry();
        $this->_helperData = $helperData;
        $this->_configurableProTypeModel = $configurableProTypeModel;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return array Product Deal Detail
     */
    public function getCurrentProductDealDetail()
    {
        $curPro = $this->_coreRegistry
                                ->registry('current_product');
        return $this->_helperData->getProductDealDetail($curPro);
    }

    /**
     * @param integer $proId
     *
     * @return array
     */
    public function getChildProductDealDetail($proId)
    {
        $product = $this->productRepository->getById($proId);
        return $this->_helperData->getProductDealDetail($product);
    }

    /**
     * get current product type
     *
     * @return string
     */
    public function getCurrentProduct()
    {
        return $this->_coreRegistry
                                ->registry('current_product');
    }

    /**
     * get all associated product of current product
     *
     * @return array
     */
    public function getAllAssociatedProducts()
    {
        $proId = $this->_coreRegistry
                                ->registry('current_product')->getId();
        $childProductsIds = $this->_configurableProTypeModel->getChildrenIds($proId);
        return $childProductsIds[0];
    }

    /**
     * get all associated product of grouped product
     *
     * @return array
     */
    public function getAllGroupedAssociatedProducts()
    {
        $ids = [];
        $product = $proId = $this->_coreRegistry
                                ->registry('current_product');
        $products = $product->getTypeInstance(true)->getAssociatedProducts($product);
        foreach ($products as $p) {
            $ids[$p->getId()] = $p->getId();
        }
        return $ids;
    }

    public function getBundleProductOptions()
    {
        $productId = $this->_coreRegistry->registry('current_product')->getId();
        $product =$this->productRepository->getById($productId);
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $productsArray = [];
        foreach ($selectionCollection as $proselection) {
            $selectionArray = [];
            $selectionArray['selection_product_name'] = $proselection->getName();
            $selectionArray['selection_product_id'] = $proselection->getProductId();
            $productsArray[$proselection->getOptionId()][$proselection->getSelectionId()] = $selectionArray;
        }
        return $productsArray;
    }
}
