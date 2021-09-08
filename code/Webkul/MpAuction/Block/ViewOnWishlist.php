<?php

/**
 * Webkul_MpAuction ProductListUpdateForAuction plugin.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Block;

use Magento\Catalog\Model\Product;

class ViewOnWishlist extends \Magento\Framework\View\Element\Template
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
        \Webkul\MpAuction\Helper\Data $auctionHelperData,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->product           = $product;
        $this->auctionHelperData = $auctionHelperData;
        parent::__construct($context, $data);
    }

    public function getAutionDetails($productId)
    {
        $product = $this->product->load($productId);
        $auctionDetail = $this->auctionHelperData->getProductAuctionDetail($product);
        return $auctionDetail;
    }

    public function getProAuctionType($productId)
    {
        $product = $this->product->load($productId);
        $auctionDetail = $this->auctionHelperData->getProAuctionType($product);
        return $auctionDetail;
    }
}
