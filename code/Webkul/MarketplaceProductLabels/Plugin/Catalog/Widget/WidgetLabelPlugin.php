<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceProductLabels\Plugin\Catalog\Widget;

class WidgetLabelPlugin
{
    /**
     * @var Magento\Catalog\Model\Product
     */
    protected $productInfo;

    /**
     * plugin to add the input tag with value of product_label_id attribute to the price of a product
     * return  according to product product_label_id attribute.
     *
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $product,
     *
     * @return html
     */
    public function afterGetProductPriceHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $product,
        $result
    ) {
        if (array_key_exists("product_label_id", $this->productInfo->getData())) {
            return $result.'<input type="hidden" class="categoryPageImageLabel" 
            value="'.$this->productInfo->getData()['product_label_id'].'">';
        } else {
            return $result ;
        }
    }

    /**
     * beforeGetProductPrice plugin to assignt the product model to a variable
     *
     * @param \Magento\Checkout\Block\Cart\Crosssell $crosssell
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function beforeGetProductPriceHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $list,
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        $this->productInfo = $product ;
    }
}
