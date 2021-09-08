<?php
namespace Webkul\MpDailyDeal\Block\Plugin;

/**
 * Webkul MpDailyDeal ProductListUpdateForDeals plugin.
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;
use Magento\GroupedProduct\Model\Product\Type\Grouped as Grouped;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class ProductListUpdateForDeals
{
    /**
     * @var \Magento\GroupedProductt\Model\Product\Type\Grouped
     */
    private $Grouped;
    /**
     * @var \Webkul\MpDailyDeals\Helper\Data
     */
    public $mpDailyDealHelper;

    /**
     * @param Webkul\MpDailyDeals\Helper\Data $dailyDealHelper
     */
    public function __construct(
        \Webkul\MpDailyDeal\Helper\Data $mpDailyDealHelper,
        ConfigurableProTypeModel $configurableProTypeModel,
        Product $product,
        Grouped $grouped,
        \Magento\Bundle\Model\Product\Type $bundleType,
        PricingHelper $pricingHelper,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceModel
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->grouped = $grouped;
        $this->bundleType = $bundleType;
        $this->_configurableProTypeModel = $configurableProTypeModel;
        $this->_product = $product;
        $this->mpDailyDealHelper = $mpDailyDealHelper;
        $this->productResourceModel = $productResourceModel;
    }
 
    /**
     * beforeGetProductPrice // update deal details before price render
     * @param ListProduct                    $list
     * @param \Magento\Catalog\Model\Product $product
     */
    public function beforeGetProductPrice(
        ListProduct $list,
        $product
    ) {
        $dealDetail = $this->mpDailyDealHelper->getProductDealDetail($product);
    }

    /**
     * aroundGetProductPrice // add clock data html product price
     * @param ListProduct                    $list
     * @param Object                         $proceed
     * @param \Magento\Catalog\Model\Product $product
     */
    public function aroundGetProductPrice(
        ListProduct $list,
        $proceed,
        $product
    ) {
        $dealDetailHtml = "";
        $type = $product->getTypeId();
        
        if ($type != 'grouped' && $type !='configurable' && $type != 'bundle') {
            $dealDetail = $this->mpDailyDealHelper->getProductDealDetail($product);
            if ($dealDetail && $dealDetail['deal_status'] && isset($dealDetail['diff_timestamp'])) {
                $dealDetailHtml = '<div class="deal wk-daily-deal" data-deal-id="'.$product->getId()
                    .'" data-update-url="'.$dealDetail['update_url'].'">
                    <div class="wk-deal-off-box"><div class="wk-deal-left-border">
                    </div><span class="deal-price-label">'
                    .$dealDetail['discount-percent'].'% '.__('Off')
                    .'</span></div><span class="price-box ">';
                $saveBox = isset($dealDetail['saved-amount'])? '<p class="wk-save-box "><span class="save-label">'
                    . __('Save On Deal ').'<span class="wk-deal-font-weight-600">'.$dealDetail['saved-amount']
                    .'</span></span></p>' : '' ;

                $saveBoxAvl = isset($dealDetail['saved-amount']) ? '' : 'notavilable';
                $dealDetailHtml = $dealDetailHtml.'<span class="wk-deal-ends-label">'
                    .__('Deal Ends in ').'&nbsp</span><p class="wk_cat_count_clock wk-deal-font-weight-600'
                    .$saveBoxAvl.'" data-stoptime="'
                .$dealDetail['stoptime'].'" data-diff-timestamp ="'.$dealDetail['diff_timestamp']
                .'"></p>'.$saveBox.'</div>';
            }
            if (!$product->getSpecialPrice() && isset($dealDetail['special-price'])) {
                $productResourceModel = $this->productResourceModel->create();
                $productResourceModel->load($product, $product->getEntityId());
                $product->setSpecialPrice($dealDetail['special-price']);
                $product->setSpecialFromDate(date('Y-m-d', strtotime($dealDetail['deal-from-date'])));
                $product->setSpecialToDate(date('Y-m-d', strtotime($dealDetail['deal-to-date'])));
                $productResourceModel->saveAttribute($product, 'special_price');
                $productResourceModel->saveAttribute($product, 'special_from_date');
                $productResourceModel->saveAttribute($product, 'special_to_date');
            }
        } elseif ($type == 'configurable') {
            $alldeal = [];
            $alldealDetails = [];
            $associatedProducts = $this->getAllAssociatedProducts($product->getId());
            $alreadyOneMax = true;
            $maxVal = -99999999;
            foreach ($associatedProducts as $key => $value) {
                $dealDetail = $this->getChildProductDealDetail($value);
                if (isset($dealDetail['saved-amount-raw']) &&
                $dealDetail['saved-amount-raw'] &&
                $dealDetail['saved-amount-raw']>=$maxVal) {
                    $maxVal = $dealDetail['saved-amount-raw'];
                    $alldeal[$value] = $dealDetail['saved-amount'];
                    $alldealDetails[$value] = $dealDetail;
                }
            }
            $dealDetail = [];
            arsort($alldeal);
            $i = 1;
            foreach ($alldeal as $key => $value) {
                if ($i == 1) {
                    $dealDetail = $alldealDetails[$key];
                }
            }
            if ($dealDetail) {
                $dealDetailHtml = '<div class="deal wk-daily-deal" data-deal-id="'.$product->getId()
                                    .'" data-update-url="'
                                    .$dealDetail['update_url'].'"><div class="wk-deal-off-box">
                                    <div class="wk-deal-left-border"></div><span class="deal-price-label">
                                    '.$dealDetail['discount-percent'].'% '.__('Off')
                                    .'</span></div><span class="price-box ">';
                $saveBox = isset($dealDetail['saved-amount'])? '<p class="wk-save-box "><span class="save-label">'
                    . __('Max Save On Deal ').'<span class="wk-deal-font-weight-600">'
                    .$dealDetail['saved-amount'].'</span></span></p>' : '' ;

                $saveBoxAvl = isset($dealDetail['saved-amount']) ? '' : 'notavilable';
                $dealDetailHtml = $dealDetailHtml.'<span class="wk-deal-ends-label">'
                    .__('Deal Ends in ').'&nbsp</span><p class="wk_cat_count_clock wk-deal-font-weight-600'
                    .$saveBoxAvl.'" data-stoptime="'
                    .$dealDetail['stoptime'].'" data-diff-timestamp ="'.$dealDetail['diff_timestamp']
                    .'"></p>'.$saveBox.'</div>';
            }
            $product = $this->setDetails($product, $dealDetail);
        } elseif ($type == 'grouped') {
            $alldeal = [];
            $alldealDetails = [];
            $associatedProducts = $this->getAllGroupProducts($product->getId());
            $totalSaveAmount = 0;
            foreach ($associatedProducts as $key => $value) {
                $dealDetail = $this->getChildProductDealDetail($value);
                if ($dealDetail && $dealDetail['deal_status']
                && isset($dealDetail['diff_timestamp'])) {
                    $alldeal[$value] = $dealDetail['saved-amount'];
                    $alldealDetails[$value] = $dealDetail;
                    if (is_numeric($dealDetail['saved-amount-raw'])) {
                        $totalSaveAmount += $dealDetail['saved-amount-raw'];
                    }
                }
            }
            $dealDetail = [];
            asort($alldeal);
            $i = 1;
            foreach ($alldeal as $key => $value) {
                $dealDetail = $alldealDetails[$key];
                break;
            }
            if ($dealDetail) {
                $dealDetailHtml = '<div class="deal wk-daily-deal" data-deal-id="'.$product->getId()
                                    .'" data-update-url="'
                                    .$dealDetail['update_url'].'"><div class="wk-deal-off-box">
                                    <div class="wk-deal-left-border"></div><span class="deal-price-label">
                                    '.$dealDetail['discount-percent'].'% '.__('Off')
                                    .'</span></div><span class="price-box ">';
                $saveBox = $totalSaveAmount? '<p class="wk-save-box "><span class="save-label">'
                    . __('Save On Deal ').'<span class="wk-deal-font-weight-600">'
                    .$this->pricingHelper->currency($totalSaveAmount, true, false).'</span></span></p>' : '' ;

                $saveBoxAvl = $totalSaveAmount ? '' : 'notavilable';
                $dealDetailHtml = $dealDetailHtml.'<span class="wk-deal-ends-label">'
                    .__('Deal Ends in ').'&nbsp</span><p class="wk_cat_count_clock wk-deal-font-weight-600'
                    .$saveBoxAvl.'" data-stoptime="'
                    .$dealDetail['stoptime'].'" data-diff-timestamp ="'.$dealDetail['diff_timestamp']
                    .'"></p>'.$saveBox.'</div>';
            }
            $product = $this->setDetails($product, $dealDetail);
        } elseif ($type == 'bundle') {
            if (!$product->getPriceType()) {
                $alldeal = [];
                $alldealDetails = [];
                $associatedProducts = $this->getAllBundleProducts($product->getId());
                $totalSaveAmount = 0;
                foreach ($associatedProducts as $key => $value) {
                    $dealDetail = $this->getChildProductDealDetail($value);
                    if ($dealDetail && $dealDetail['deal_status']
                    && isset($dealDetail['diff_timestamp'])) {
                        $alldeal[$value] = $dealDetail['saved-amount'];
                        $alldealDetails[$value] = $dealDetail;
                        if (is_numeric($dealDetail['saved-amount-raw'])) {
                            $totalSaveAmount += $dealDetail['saved-amount-raw'];
                        }
                    }
                }
                $dealDetail = [];
                asort($alldeal);
                $i = 1;
                foreach ($alldeal as $key => $value) {
                    $dealDetail = $alldealDetails[$key];
                    break;
                }
                if ($dealDetail) {
                    $dealDetailHtml = '<div class="deal wk-daily-deal" data-deal-id="'.$product->getId()
                                        .'" data-update-url="'
                                        .$dealDetail['update_url'].'"><div class="wk-deal-off-box">
                                        <div class="wk-deal-left-border"></div><span class="deal-price-label">
                                        '.$dealDetail['discount-percent'].'% '.__('Off')
                                        .'</span></div><span class="price-box ">';
                    $saveBox = $totalSaveAmount? '<p class="wk-save-box "><span class="save-label">'
                        . __('Save On Deal ').'<span class="wk-deal-font-weight-600">'
                        .$this->pricingHelper->currency($totalSaveAmount, true, false).'</span></span></p>' : '' ;

                    $saveBoxAvl = $totalSaveAmount ? '' : ' ';
                    $dealDetailHtml = $dealDetailHtml.'<span class="wk-deal-ends-label">'
                        .__('Deal Ends in ').'&nbsp</span><p class="wk_cat_count_clock wk-deal-font-weight-600'
                        .$saveBoxAvl.'" data-stoptime="'
                        .$dealDetail['stoptime'].'" data-diff-timestamp ="'.$dealDetail['diff_timestamp']
                        .'"></p>'.$saveBox.'</div>';
                }
                $product = $this->setDetails($product, $dealDetail);
            }

            $dealDetail = $this->mpDailyDealHelper->getProductDealDetail($product);
            if ($dealDetail && $dealDetail['deal_status'] && isset($dealDetail['diff_timestamp'])) {
                $dealDetailHtml = '<div class="deal wk-daily-deal" data-deal-id="'.$product->getId()
                    .'" data-update-url="'.$dealDetail['update_url'].'">
                    <div class="wk-deal-off-box"><div class="wk-deal-left-border">
                    </div><span class="deal-price-label">'
                    .$dealDetail['discount-percent'].'% '.__('Off')
                    .'</span></div><span class="price-box ">';
                $saveBox = isset($dealDetail['saved-amount'])? '<p class="wk-save-box "><span class="save-label">'
                    . __('Save On Deal ').'<span class="wk-deal-font-weight-600">'.$dealDetail['saved-amount']
                    .'</span></span></p>' : '' ;

                $saveBoxAvl = isset($dealDetail['saved-amount']) ? '' : ' ';
                $dealDetailHtml = $dealDetailHtml.'<span class="wk-deal-ends-label">'
                    .__('Deal Ends in ').'&nbsp</span><p class="wk_cat_count_clock wk-deal-font-weight-600'
                    .$saveBoxAvl.'" data-stoptime="'
                .$dealDetail['stoptime'].'" data-diff-timestamp ="'.$dealDetail['diff_timestamp']
                .'"></p>'.$saveBox.'</div>';
            }
            if (!$product->getSpecialPrice() && isset($dealDetail['special-price'])) {
                $productResourceModel = $this->productResourceModel->create();
                $productResourceModel->load($product, $product->getEntityId());
                $product->setSpecialPrice($dealDetail['special-price']);
                $product->setSpecialFromDate(date('Y-m-d', strtotime($dealDetail['deal-from-date'])));
                $product->setSpecialToDate(date('Y-m-d', strtotime($dealDetail['deal-to-date'])));
                $productResourceModel->saveAttribute($product, 'special_price');
                $productResourceModel->saveAttribute($product, 'special_from_date');
                $productResourceModel->saveAttribute($product, 'special_to_date');
            }
        }
        return $proceed($product).$dealDetailHtml;
    }

    /**
     * Get Associated product details
     *
     * @param int $proId
     * @return array
     */
    public function getChildProductDealDetail($proId)
    {
        $product = $this->_product->load($proId);
        return $this->mpDailyDealHelper->getProductDealDetail($product);
    }

    /**
     * Get Associated product
     *
     * @param int $id
     * @return array
     */
    public function getAllAssociatedProducts($id)
    {
        $childProductsIds = $this->_configurableProTypeModel->getChildrenIds($id);
        return $childProductsIds[0];
    }
    public function getAllGroupProducts($id)
    {
        $childProductsIds= $this->grouped->getChildrenIds($id);
        return array_shift($childProductsIds);
    }

    public function getAllBundleProducts($id)
    {
        $childProductsIds= $this->bundleType->getChildrenIds($id);
        return array_shift($childProductsIds);
    }
    /**
     * Set Special Price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $dealDetail
     * @return \Magento\Catalog\Model\Product
     */
    private function setDetails($product, $dealDetail)
    {
        if (!$product->getSpecialPrice() && isset($dealDetail['special-price'])) {
            $productResourceModel = $this->productResourceModel->create();
            $productResourceModel->load($product, $product->getEntityId());
            $product->setSpecialPrice($dealDetail['special-price']);
            $product->setSpecialFromDate(date('Y-m-d', strtotime($dealDetail['deal-from-date'])));
            $product->setSpecialToDate(date('Y-m-d', strtotime($dealDetail['deal-to-date'])));
            $productResourceModel->saveAttribute($product, 'special_price');
            $productResourceModel->saveAttribute($product, 'special_from_date');
            $productResourceModel->saveAttribute($product, 'special_to_date');
        }
        return $product;
    }
}
