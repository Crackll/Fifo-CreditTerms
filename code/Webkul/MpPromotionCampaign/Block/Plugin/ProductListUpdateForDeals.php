<?php
/**
 * Webkul MpPromotionCampaign ProductListUpdateForDeals plugin.
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPromotionCampaign\Block\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigProType;
use Magento\Catalog\Model\ProductFactory;

class ProductListUpdateForDeals
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configProType;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Webkul\MpPromotionCampaign\Helper\Data
     */
    private $helper;

    /**
     * @param ConfigProType $configProType
     * @param Product $product
     * @param Webkul\DailyDeals\Helper\Data $dailyDealHelper
     */
    public function __construct(
        ConfigProType $configProType,
        ProductFactory $product,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\MpPromotionCampaign\Helper\Data $helper
    ) {

        $this->logger = $logger;
        $this->configProType = $configProType;
        $this->product = $product;
        $this->helper = $helper;
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
        $campaignDetail = $this->helper->getProductCampainDetail($product);
    }
}
