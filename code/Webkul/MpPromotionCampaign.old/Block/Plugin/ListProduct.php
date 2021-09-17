<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPromotionCampaign\Block\Plugin;

use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;
 
class ListProduct
{

    protected $request;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory $campaignProduct
    ) {
        $this->campaignProduct = $campaignProduct;
        $this->request = $request;
    }

    public function afterGetLoadedProductCollection(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        $productInfo,
        $requestInfo = null
    ) {
        $campaignId = $this->request->getParam('id');
        $campaignProduct = $this->campaignProduct->create()
                            ->addFieldToFilter('campaign_id', $campaignId)
                            ->addFieldToFilter('status', CampaignProModel::STATUS_JOIN);
        $productIds = [];
        foreach ($campaignProduct->getData() as $product) {
            $productIds[] = $product['product_id'];
        }
    }
}
