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

class ProductListDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
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
        \Magento\Framework\App\ResourceConnection $resource,
        HelperData $helperData,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
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
        $this->_resource = $resource;
        $sellerId = $helperData->getCustomerId();
        $marketplaceProduct = $marketplacecollectionFactory->create()
        ->addFieldToFilter('seller_id', $sellerId);
      
        $allIds = [];
        $campaignId = '';
        $campaignId =  $request->getParam('id');
        if (empty($campaignId)) {
            $url = $redirect->getRefererUrl();
            $urlData = $helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
        }
        $allIds = $marketplaceProduct->getAllIds();
        $campaignData = $campaignProduct->create()->getCollection()
                        ->addFieldToFilter('campaign_id', $campaignId)
                        ->addFieldToFilter('status', ['neq'=>'0']);
        foreach ($campaignData->getData() as $data) {
            if (in_array($data['product_id'], $allIds)) {
                $match = array_search($data['product_id'], $allIds);
                unset($allIds[$match]);
            }
        }
        /** @var Collection $collection */
        $collectionData = $productCollection->create();
        $collectionData->addAttributeToSelect('status');
        $collectionData->addFieldToFilter('entity_id', ['in' => $allIds])
        ->addAttributeToFilter('type_id', ['nin'=>'configurable']);
        ;
        $cataloginventory = $this->_resource->getTableName('cataloginventory_stock_item');
        $collectionData->joinField(
            'qty',
            $cataloginventory,
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
        $collectionData->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()]);
        $collectionData->setFlag('has_stock_status_filter');
        $productCampaign = $this->_resource->getTableName('mppromotionseller_product_campaign');
        $collectionData->getSelect()->columns(['sellerQty' =>
        new \Zend_Db_Expr('(SELECT qty FROM '.$productCampaign.'
         WHERE campaign_id='.$campaignId.' and product_id=e.entity_id AND seller_campaign_id='.$sellerId.')')]);
         $collectionData->getSelect()->columns(['sellerPrice' =>
         new \Zend_Db_Expr('(SELECT price FROM '.$productCampaign.'
          WHERE campaign_id='.$campaignId.'  and product_id=e.entity_id AND seller_campaign_id='.$sellerId.')')]);
        $this->collection = $collectionData;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }
}
