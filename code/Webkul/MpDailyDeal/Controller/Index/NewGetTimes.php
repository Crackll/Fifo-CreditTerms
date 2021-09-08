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

class NewGetTimes extends Action
{

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonData

    ) {
         
        $this->jsonData = $jsonData;
        parent::__construct($context);
    }

    /**
     * MpDailyDeal Product Collection Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        die("line43+1");
        // try {
        //     $collection = $this->getProductCollection();
        //     $result = [];
        //     foreach ($collection as $value) {
        //         $product = $this->productFactory->create()->load($value->getId());
        //         $dealDetail  = $this->getCurrentProductDealDetail($value);
        //         if ($value["type_id"]=="grouped") {
        //             foreach ($dealDetail as $deal) {
        //                 $parent = $this->grouped->getParentIdsByChild($dealDetail['entity_id']);
        //                 $result[$parent[0]] = $dealDetail;
        //                 $dealDetail['parent'] = $parent;
        //                 $result[$dealDetail['entity_id']] = $dealDetail;
        //             }
        //         } elseif ($value["type_id"]=="configurable") {
        //             $dealDetail  = $this->getCurrentProductDealDetail($value);
        //             foreach ($dealDetail as $deal) {
        //                 $parent= $this->configurable->getParentIdsByChild($dealDetail['entity_id']);
        //                 $result[$parent[0]] = $dealDetail;
        //                 $dealDetail['parent'] = $parent;
        //                 $result[$dealDetail['entity_id']] = $dealDetail;
        //             }
        //         } elseif ($value['type_id']=="bundle" && !$product->getPriceType()) {
                    
        //                 $tempMaxBundleDeal = -1;
        //                 $dealDetail  = $this->getCurrentProductDealDetail($value);
        //             foreach ($dealDetail as $deal) {
        //                 if ($deal['discount-percent']>$tempMaxBundleDeal) {
        //                     $tempMaxBundleDeal = $deal['discount-percent'];
        //                     $parent=$this->bundle->getParentIdsByChild($deal['entity_id']);
        //                     $deal['parent'] = $parent;
        //                     $result[$product->getId()] = $deal;
        //                 }
        //             }
                    
        //             $dataDeal = $this->helper->getProductDealDetail($value);
        //             if ($dataDeal) {
        //                 $dataDeal['entity_id'] = $value->getId();
        //                 $result[$dataDeal['entity_id']] = $dataDeal;
        //             }
        //         } else {
        //             $dealDetail  = $this->getCurrentProductDealDetail($value);
        //             if (!empty($dealDetail)) {
        //                 $result[$dealDetail['entity_id']] = $dealDetail;
        //             }
        //         }

        //     }
            
        //     $this->getResponse()->setHeader('Content-type', 'application/javascript');
        //     $this->getResponse()->setBody($this->jsonData
        //         ->jsonEncode(
        //             [
        //                 'success' => 1,
        //                 'data' => $result
        //             ]
        //         ));
        
        // } catch (\Exception $e) {
        //     die("line125");
        //     $this->getResponse()->setHeader('Content-type', 'application/javascript');
        //     $this->getResponse()->setBody($this->jsonData
        //         ->jsonEncode(
        //             [
        //                 'success' => 0,
        //                 'message' => __('Something went wrong in getting spin wheel.')
        //             ]
        //         ));
        // }
    }
   
}
