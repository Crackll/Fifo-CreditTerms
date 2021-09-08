<?php

/**
 * Webkul_MpAuction ProductListUpdateForAuction plugin.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Block\Plugin;

use Magento\Catalog\Model\Product;

class WishListUpdateForAuction
{
    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $auctionHelperData;

    /**
     * @var product
     */
    private $product;

    public function __construct(
        Product $product,
        \Webkul\MpAuction\Helper\Data $auctionHelperData
    ) {
        $this->product           = $product;
        $this->auctionHelperData = $auctionHelperData;
    }

    public function aroundGetAddToCartQty(
        \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart $result,
        $proceed,
        $item
    ) {
        
        $product = $this->product->load($item->getProductId());
        $auctionDetail = $this->auctionHelperData->getProductAuctionDetail($product);
        $this->auctionHelperData->printLog($proceed($item).$auctionDetail);
        return $proceed($item).$auctionDetail;
    }
}
