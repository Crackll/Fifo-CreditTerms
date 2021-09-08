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
use Magento\Framework\Pricing\Render;

class WishlistSidebarForAuction
{
    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $auctionHelperData;

    public function __construct(
        \Webkul\MpAuction\Helper\Data $auctionHelperData
    ) {
        $this->auctionHelperData = $auctionHelperData;
    }

    public function aroundGetProductPriceHtml(
        \Magento\Wishlist\Block\Customer\Sidebar $subject,
        $proceed,
        $product,
        $priceType,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        $auctionDetail = $this->auctionHelperData->getProductAuctionDetail($product);
        return $proceed($product, $priceType, $renderZone, $arguments).$auctionDetail;
    }
}
