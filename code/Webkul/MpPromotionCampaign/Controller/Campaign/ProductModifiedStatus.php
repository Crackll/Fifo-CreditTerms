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

namespace Webkul\MpPromotionCampaign\Controller\Campaign;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class ProductModifiedStatus extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Result Page
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Marketplace Helper
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $marketplaceHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaign,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->product = $product;
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->campaign = $campaign;
        $this->campaignProduct = $campaignProduct;
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $sellerId = $this->marketplaceHelper->getCustomerId();
        $data = $this->getRequest()->getPost();
        $postData = [];
        foreach ($data['items'] as $record) {
            $postData = $record;
        }
        $data = $postData;
        if ($data) {
            $url = $this->redirect->getRefererUrl();
            $urlData = $this->helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
            $campaignStatus = $this->helper->campaignStatus($campaignId);
            if ($campaignStatus['code'] == 2) {
                $this->dataUpdate($data, $campaignId, $sellerId);
            } else {
                $message =[];
                if ($campaignStatus['code'] == 1) {
                    $message[] =  __("Campaign already start. You can't update product");
                    return $resultJson->setData([
                        'messages' =>$message,
                        'error' => true
                    ]);
                }
                if ($campaignStatus['code'] == 3) {
                    $message[] =  __("Campaign expired. You can't update product");
                    return $resultJson->setData([
                        'messages' =>$message,
                        'error' => true
                    ]);
                }
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    public function dataUpdate($data, $campaignId, $sellerId)
    {
        $productPrice = $this->product->create()->load($data['product_id'])->getPrice();
        $checkProduct = $this->campaignProduct->create()->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId)
        ->addFieldToFilter('seller_campaign_id', $sellerId)
        ->addFieldToFilter('product_id', $data['product_id'])->getLastItem();
    
        $campainDetail =  $this->campaign->create()->getCollection()
                        ->addFieldToFilter('entity_id', $campaignId)
                        ->getFirstItem()
                        ->getData();
        $promotionPrice = ceil($productPrice -($productPrice * $campainDetail['discount']/100));
        if ($checkProduct->getId()) {
            if (!isset($data['promotionPrice'])) {
                $data['promotionPrice'] = $data['price'];
            }
            if ($data['promotionPrice'] <= $promotionPrice) {
                $status = 1;
                if ($checkProduct->getStatus() == 0) {
                    $status = 0;
                }
                $checkProduct->setCampaignId($campaignId)
                ->setSellerCampaignId($sellerId)
                ->setProductId($data['product_id'])
                ->setPrice($data['promotionPrice'])
                ->setStatus($status)
                ->setQty($data['qty'])->save();
                $message[] = __('Update successfully');
                return $resultJson->setData([
                'messages' => $message,
                'error' => false
                ]);
            } else {
                $message[] =  __('You can set promation price greater than or equal to '.$promotionPrice);
                return $resultJson->setData([
                'messages' =>$message,
                'error' => true
                ]);
            }
        } else {
            if (!isset($data['promotionPrice'])) {
                $data['promotionPrice'] = $data['price'];
            }
      
            if ($data['promotionPrice'] <= $promotionPrice) {
                $productData = $this->campaignProduct->create()
                ->setCampaignId($campaignId)
                ->setSellerCampaignId($sellerId)
                ->setProductId($data['product_id'])
                ->setPrice($data['promotionPrice'])
                ->setQty($data['qty'])->save();
                $message[] = __('Update successfully');
                return $resultJson->setData([
                'messages' => $message,
                'error' => false
                ]);
            } else {
                $message[] =  __('You can set promation price greater than or equal to '.$promotionPrice);
                return $resultJson->setData([
                'messages' =>$message,
                'error' => true
                ]);
            }
        }
    }
}
