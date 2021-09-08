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
namespace Webkul\MpPromotionCampaign\Model;
 
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;
 
class Layer extends \Magento\Catalog\Model\Layer
{
    public function __construct(
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Bundle\Model\ResourceModel\Selection  $bundle,
        // \Magento\Bundle\Model\Product\Type $bundle,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->bundle = $bundle;
        $this->configurable = $configurable;
        $this->campaignProduct = $campaignProduct;
        $this->request = $request;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
    }
 
    public function getProductCollection()
    {
        if (isset($this->_productCollections['webkul_custom'])) {
            $collection = $this->_productCollections['webkul_custom'];
        } else {
            $campaignData = $this->campaignProduct->create()
                            ->getCollection()
                            ->addFieldToFilter('campaign_id', $this->request->getParam('id'))
                            ->addFieldToFilter('status', CampaignProModel::STATUS_JOIN);
            $productIds =[];
            foreach ($campaignData->getData() as $campaign) {
                //configurable associated products those visibility is catalog, search
                $productIds[] = $campaign['product_id'];
                //configurable product
                $product = $this->configurable->getParentIdsByChild($campaign['product_id']);
                if (isset($product[0])) {
                    $productIds[] =$product[0];
                } else {
                    $bundleProduct = $this->bundle->getParentIdsByChild($campaign['product_id']);
                    if ($bundleProduct) {
                        $productIds[] =$bundleProduct[0];
                    } else {
                        $productIds[] = $campaign['product_id'];
                    }
                }
            }
            $collection = $this->productCollectionFactory->create()->addFieldToFilter('entity_id', ['in'=>$productIds]);
           
            $this->prepareProductCollection($collection);
            $this->_productCollections['webkul_custom'] = $collection;
        }
        return $collection;
    }
}
