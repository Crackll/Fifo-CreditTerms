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

namespace Webkul\MpPromotionCampaign\Block;

use Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\Grid\CollectionFactory;

class Campaign extends \Magento\Framework\View\Element\Template
{
     /**
      * Constructor
      *
      * @param \Magento\Framework\View\Element\Template\Context $context,
      * @param CollectionFactory $campaignFactory,
      * @param \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory $campaignProduct,
      * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper,
      * @param \Webkul\MpPromotionCampaign\Helper\Data $mppromotionHelper,
      * @param \Webkul\MpPromotionCampaign\Model\CampaignJoinFactory $campaignJoin,
      * @param array $data
      */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $campaignFactory,
        \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory $campaignProduct,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpPromotionCampaign\Helper\Data $mppromotionHelper,
        \Webkul\MpPromotionCampaign\Model\CampaignJoinFactory $campaignJoin,
        array $data = []
    ) {
        $this->campaignProduct = $campaignProduct;
        $this->campaignJoin = $campaignJoin;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->mppromotionHelper = $mppromotionHelper;
        $this->campaignFactory = $campaignFactory;
        parent::__construct($context, $data);
    }

     /**
      * Get totalSeller
      *
      * @return total number of seller of campaign
      */

    public function totalSeller($campaignID)
    {
        $sellerCampaign = $this->campaignJoin->create()->getCollection()
        ->addFieldToFilter('campaign_id', $campaignID);
        return $sellerCampaign->getSize();
    }
    
    /**
     * Get totalProduct
     *
     * @return total number of product of campaign
     */

    public function totalProduct($campaignID)
    {
        $campaignProduct = $this->campaignProduct->create()
        ->addFieldToFilter('campaign_id', $campaignID);
        return $campaignProduct->getSize();
    }
     /**
      * Get campainSellerJoin
      *
      * @return boolean
      */

    public function campainSellerJoin($campaignID)
    {
        $sellerId = $this->marketplaceHelper->getCustomerId();
        $sellerCampaign = $this->campaignJoin->create()->getCollection()
        ->addFieldToFilter('campaign_id', $campaignID)
        ->addFieldToFilter('seller_id', $sellerId);
        if ($sellerCampaign->getSize()) {
            return true;
        } else {
            return false;
        }
    }

    public function getmppromotionHelper()
    {
        return $this->mppromotionHelper;
    }
}
