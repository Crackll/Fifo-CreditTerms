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

class CampaignProduct extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
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
        \Magento\Eav\Model\Attribute $eavAttribute,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
        MarketplaceProductCollection $marketplacecollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        HelperData $helperData,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->_resource = $resource;
        $this->request = $request;
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
        
        $attributeData = $this->eavAttribute->getCollection()
                        ->addFieldToFilter('entity_type_id', '4')
                        ->addFieldToFilter('attribute_code', 'name')
                        ->getFirstItem();
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
        $collectionData->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
       
        $sellerProductCampaign = $this->_resource->getTableName('mppromotionseller_product_campaign');
        $collectionData->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()]);
        $collectionData->setFlag('has_stock_status_filter');
        $collectionData->getSelect()->join(
            $sellerProductCampaign.' as cam',
            'e.entity_id = cam.product_id',
            ['quantity'=>'at_qty.qty','price'=>'cam.price',
            'id'=>'cam.entity_id',
            'productStatus'=>'cam.status',
            'qty'=>'cam.qty']
        )->where('cam.campaign_id = '.$campaignId)->where('cam.status !=0');
      
        if (isset($request->getParam('filters')['qty'])) {
            $collectionData->getSelect()->where('cam.qty ='.$request->getParam('filters')['qty']);
        }
        $this->collection = $collectionData;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'productStatus') {
            $data = $this->request->getParams();
            $productStatus = $data['filters']['productStatus'];
            $this->getCollection()->getSelect()->where('cam.status='.$productStatus);
        } elseif ($filter->getField() == 'price') {
            $data = $this->request->getParams();
            $campaignPriceFrom = $data['filters']['price']['from'];
            $campaignPriceTo = $data['filters']['price']['to'];
            $this->getCollection()->getSelect()
            ->where('cam.price between '.$campaignPriceFrom.' and '.$campaignPriceTo);
        } else {
            $this->getCollection()->addFieldToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        }
    }
}
