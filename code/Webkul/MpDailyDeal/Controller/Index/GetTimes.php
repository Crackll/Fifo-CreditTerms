<?php
/**
 * Webkul_MpDailyDeal Collection controller.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as Grouped;
use Magento\Bundle\Model\Product\Type as bundle;

class GetTimes extends Action
{

     /**
      * @var \Magento\GroupedProductt\Model\Product\Type\Bundle
      */
    private $bundle;
    /**
     * @var \Magento\GroupedProductt\Model\Product\Type\Grouped
     */
    private $Grouped;
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Webkul\MpDailyDeal\Helper\Data $helper,
        Grouped $grouped,
        \Magento\Bundle\Model\Product\Type $bundle
    ) {
        $this->bundle = $bundle;
        $this->grouped = $grouped;
        $this->jsonData = $jsonData;
        $this->helper = $helper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->configurable = $configurable;
        $this->today = $timezone->convertConfigTimeToUtc($timezone->date());
        parent::__construct($context);
    }

    /**
     * MpDailyDeal Product Collection Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $collection = $this->getProductCollection();
            $result = [];
            foreach ($collection as $value) {
                $product = $this->productFactory->create()->load($value->getId());
                $dealDetail  = $this->getCurrentProductDealDetail($value);
                if ($value["type_id"]=="grouped") {
                    foreach ($dealDetail as $deal) {
                        $parent = $this->grouped->getParentIdsByChild($dealDetail['entity_id']);
                        $result[$parent[0]] = $dealDetail;
                        $dealDetail['parent'] = $parent;
                        $result[$dealDetail['entity_id']] = $dealDetail;
                    }
                } elseif ($value["type_id"]=="configurable") {
                    $dealDetail  = $this->getCurrentProductDealDetail($value);
                    foreach ($dealDetail as $deal) {
                        $parent= $this->configurable->getParentIdsByChild($dealDetail['entity_id']);
                        $result[$parent[0]] = $dealDetail;
                        $dealDetail['parent'] = $parent;
                        $result[$dealDetail['entity_id']] = $dealDetail;
                    }
                } elseif ($value['type_id']=="bundle" && !$product->getPriceType()) {
                    
                        $tempMaxBundleDeal = -1;
                        $dealDetail  = $this->getCurrentProductDealDetail($value);
                    foreach ($dealDetail as $deal) {
                        if ($deal['discount-percent']>$tempMaxBundleDeal) {
                            $tempMaxBundleDeal = $deal['discount-percent'];
                            $parent=$this->bundle->getParentIdsByChild($deal['entity_id']);
                            $deal['parent'] = $parent;
                            $result[$product->getId()] = $deal;
                        }
                    }
                    
                    $dataDeal = $this->helper->getProductDealDetail($value);
                    if ($dataDeal) {
                        $dataDeal['entity_id'] = $value->getId();
                        $result[$dataDeal['entity_id']] = $dataDeal;
                    }
                } else {
                    $dealDetail  = $this->getCurrentProductDealDetail($value);
                    if (!empty($dealDetail)) {
                        $result[$dealDetail['entity_id']] = $dealDetail;
                    }
                }

            }
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody($this->jsonData
                ->jsonEncode(
                    [
                        'success' => 1,
                        'data' => $result
                    ]
                ));
        
        } catch (\Exception $e) {
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody($this->jsonData
                ->jsonEncode(
                    [
                        'success' => 0,
                        'message' => __('Something went wrong in getting spin wheel.')
                    ]
                ));
        }
    }

    /**
     * @return Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function getProductCollection()
    {
        $simpledealIds = $this->productCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('deal_status', 1)
            ->addFieldToFilter('visibility', ['neq' => 1])
            ->addAttributeToFilter(
                'deal_from_date',
                ['lt'=>$this->today]
            )->addAttributeToFilter(
                'deal_to_date',
                ['gt'=>$this->today]
            )->getColumnValues('entity_id');
        $notvisibleIds = $this->productCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('deal_status', 1)
            ->addFieldToFilter('visibility', ['eq' => 1])
            ->addAttributeToFilter(
                'deal_from_date',
                ['lt'=>$this->today]
            )->addAttributeToFilter(
                'deal_to_date',
                ['gt'=>$this->today]
            )->getColumnValues('entity_id');
        $configurableIds = [0];
        $groupnotvisible=[0];
        $bundlenotvisible=[0];
        $groupproIds= [0];
        $bundleproIds= [0];
        foreach ($simpledealIds as $notvisiblesimpleIds) {
            $groupparentIds = $this->grouped->getParentIdsByChild($notvisiblesimpleIds);
            $groupproIds= [...$groupproIds, ...$simpledealIds, ...$groupparentIds];
            $bundleparentIds = $this->bundle->getParentIdsByChild($notvisiblesimpleIds);
            $bundleproIds= [...$bundleproIds, ...$simpledealIds, ...$bundleparentIds];
        }

        foreach ($notvisibleIds as $notvisibleId) {
            $parentIds = $this->configurable->getParentIdsByChild($notvisibleId);
            $groupparentnotvisible = $this->grouped->getParentIdsByChild($notvisibleId);
            $groupnotvisible=[...$groupnotvisible, ...$groupparentnotvisible];
            $bundleparentnotvisible = $this->bundle->getParentIdsByChild($notvisibleId);
            $bundlenotvisible=[...$bundlenotvisible,...$bundleparentnotvisible];
            $configurableIds = [...$configurableIds,...$parentIds];
        }
        $bundleProIds = $this->productCollectionFactory
            ->create()
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('price_type')
            ->addFieldToFilter('type_id', 'bundle')
            ->addFieldToFilter('price_type', '0')
            ->addFieldToFilter('entity_id', ['in'=>[...$bundleproIds,...$bundlenotvisible]])
            ->getColumnValues('entity_id');
        $allProductIds = array_merge($simpledealIds, $configurableIds, $groupnotvisible, $groupproIds, $bundleProIds);
        $collection = $this->productCollectionFactory
                        ->create()
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('entity_id', ['in'=>$allProductIds]);
        return $collection;
    }

        /**
         * @return array Product Deal Detail
         */

    public function getCurrentProductDealDetail($curPro)
    {
        $productType = $curPro->getTypeId();
        $dataDeal = [];
        if ($productType == "configurable") {
            $dataDeal = $this->getConfigAssociateProDeals($curPro);
        } elseif ($productType == "grouped") {
            $dataDeal = $this->getGroupAssociateProDeals($curPro);
        } elseif ($productType == "bundle" && !$curPro->getPriceType()) {
            $dataDeal = $this->getBundleAssociateProDeals($curPro);
        } elseif ($productType == "simple") {
            $dataDeal = $this->helper->getProductDealDetail($curPro);
            if ($dataDeal) {
                $dataDeal['entity_id'] = $curPro->getId();
            }
        }
        return $dataDeal;
    }
    public function getGroupAssociateProDeals($curPro)
    {
        $groupProId = $curPro->getId();
        $alldeal = [];
        $assDealDetails = [];
        $associatedProducts= $this->grouped->getChildrenIds($groupProId);
        foreach ($associatedProducts[3] as $assProId) {
            $dealDetail = $this->getChildProductDealDetail($assProId);
            if ($dealDetail
                && isset($dealDetail['deal_status'])
                && $dealDetail['deal_status']
                && isset($dealDetail['diff_timestamp'])
            ) {
                $alldeal[$assProId] = $dealDetail['saved-amount'];
                $dealDetail['entity_id'] = $assProId;
                $dealDetail['pro_type'] = 'grouped';
                $assDealDetails[$assProId] = $dealDetail;
            }
        
            $dealDetail = $this->helper->getMaxDiscount($assDealDetails);
            if (!empty($dealDetail)) {
                return $dealDetail;
            }
        }
        return [];
    }
    public function getBundleAssociateProDeals($curPro)
    {
        $bundleProId = $curPro->getId();
        $alldeal = [];
        $assDealDetails = [];
        $associatedProducts = $this->bundle->getChildrenIds($bundleProId);
        foreach ($associatedProducts as $key => $value) {
            foreach ($value as $val) {
                $dealDetail = $this->getChildProductDealDetail($val);
                if ($dealDetail
                && isset($dealDetail['deal_status'])
                && $dealDetail['deal_status']
                && isset($dealDetail['diff_timestamp'])
                ) {
                    $alldeal[$val] = $dealDetail['saved-amount'];
                    $dealDetail['entity_id'] = $val;
                    $dealDetail['pro_type'] = 'bundle';
                    $assDealDetails[$val] = $dealDetail;
                }
            }
        }
        return $assDealDetails;
    }

    /**
     * getConfigAssociateProDeals
     * @param Magento\Catalog\Model\Product $curPro
     * @return boolen|array
     */
    
    public function getConfigAssociateProDeals($curPro)
    {
        $configProId = $curPro->getId();
        $alldeal = [];
        $associatedProducts = $this->configurable->getChildrenIds($configProId);
        foreach ($associatedProducts[0] as $assProId) {
            $dealDetail = $this->getChildProductDealDetail($assProId);
            if ($dealDetail
                && isset($dealDetail['deal_status'])
                && $dealDetail['deal_status']
                && isset($dealDetail['diff_timestamp'])
            ) {
                $alldeal[$assProId] = $dealDetail['saved-amount'];
                $dealDetail['entity_id'] = $assProId;
                $dealDetail['pro_type'] = 'configurable';
                $assDealDetails[$assProId] = $dealDetail;
            }
        }
        if (isset($assDealDetails)) {
            $dealDetail = $this->helper->getMaxDiscount($assDealDetails);
            if (!empty($dealDetail)) {
                return $dealDetail;
            }
        }
        return [];
    }

    /**
     * getChildProductDealDetail
     * @param int $proId
     * @return Magento\Catalog\Model\Product
     */
    public function getChildProductDealDetail($proId)
    {
        $product = $this->productFactory->create()->load($proId);
        $dealvalue = $product->getDealValue();
        if ($product->getDealDiscountType() == 'percent') {
            $price = $product->getPrice() * ($dealvalue/100);
            $discount = $dealvalue;
        } else {
            $price = $dealvalue;
            $discount = ($dealvalue/$product->getPrice())*100;
        }
        $dealData = $this->helper->getProductDealDetail($product);
        if (!isset($dealData['discount-percent']) ||
        (isset($dealData['discount-percent']) &&
        !$dealData['discount-percent'])) {
            $dealData['discount-percent'] = round(100-$discount);
        }
        return $dealData;
    }
}
