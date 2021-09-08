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

namespace Webkul\MpPromotionCampaign\Ui\DataProvider\Seller;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MarketplaceProductCollection;
use Webkul\Marketplace\Helper\Data as HelperData;

class ProductStatusDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    /**
     * Product collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ProductCollection $productCollection
     * @param MarketplaceProductCollection $marketplacecollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ProductCollection $productCollection,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
        MarketplaceProductCollection $marketplacecollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        HelperData $helperData,
        \Magento\Framework\App\ResourceConnection $resource,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        
        $this->_resource = $resource;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $productCollection,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $sellerId = $helperData->getCustomerId();
        $campaignId = '';
        $campaignId =  $request->getParam('id');
        if (empty($campaignId)) {
            $url = $redirect->getRefererUrl();
            $urlData = $helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
        }
     
        /** @var Collection $collection */
        $collectionData = $productCollection->create();
        $collectionData->addAttributeToSelect('status');
        $collectionData->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()]);
        $collectionData->setVisibility($productVisibility->getVisibleInSiteIds());
        $collectionData->setFlag('has_stock_status_filter');
        $productCampaign = $this->_resource->getTableName('mppromotionseller_product_campaign');
        $collectionData->getSelect()->joinLeft(
            ['cam' =>$productCampaign],
            'e.entity_id = cam.product_id',
            [
                'id'=>'cam.entity_id',
                'camPrice'=>'cam.price',
                'qty'=>'cam.qty',
                'status'=>'cam.status'
            ]
        )->where('cam.campaign_id = '.$campaignId)->where('cam.status !=0');
        $this->collection = $collectionData;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }
}
