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
namespace Webkul\MpPromotionCampaign\Ui\DataProvider\Admin;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Class ProductListDataProvider
 */
class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
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

     * @param MarketplaceProductCollection $marketplacecollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $productCollection,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
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
        $productCampaign =$this->_resource->getTableName('mppromotionseller_product_campaign');
        $campaignId = '';
        $campaignId = $request->getParam('id');
        if (empty($campaignId)) {
            $url = $redirect->getRefererUrl();
            $urlData = $helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
        }
        $campaignProduct = $campaignProduct->create()->getCollection()
                            ->addFieldToFilter('campaign_id', $campaignId)
                            ->addFieldToFilter('status', ['neq' => '0']);
        $productIds = [];
        foreach ($campaignProduct as $camPro) {
            $productIds[] = $camPro->getProductId();
        }
        if (!isset($productIds[0])) {
            $collectionData = $this->getCollection()->addAttributeToFilter('type_id', ['nin'=>'configurable']);
        } else {
            $collectionData = $this->getCollection()
                               ->addAttributeToFilter('entity_id', ['nin' => $productIds])
                               ->addAttributeToFilter('type_id', ['nin'=>'configurable'])
                               ->addAttributeToFilter('type_id', ['nin'=>'bundle']);
        }
        $collectionData->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
    
        $collectionData->getSelect()->columns(['sellerQty' =>
        new \Zend_Db_Expr('(SELECT qty FROM '.$productCampaign.'
     WHERE campaign_id='.$campaignId.' and product_id=e.entity_id)')]);
        $collectionData->getSelect()->columns(['sellerPrice' =>
        new \Zend_Db_Expr('(SELECT price FROM '.$productCampaign.'
      WHERE campaign_id='.$campaignId.'  and product_id=e.entity_id)')]);
    
        $this->collection = $collectionData;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }
}
