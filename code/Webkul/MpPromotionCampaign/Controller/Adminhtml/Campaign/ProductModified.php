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

namespace Webkul\MpPromotionCampaign\Controller\Adminhtml\Campaign;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;

class ProductModified extends \Magento\Customer\Controller\AbstractAccount
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
        $sellerId = 0;
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
            $productPrice = $this->product->create()->load($data['entity_id'])->getPrice();
            $checkProduct = $this->campaignProduct->create()->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId)
            ->addFieldToFilter('product_id', $data['entity_id'])->getLastItem();
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
                    $checkProduct->setCampaignId($campaignId)
                    ->setSellerCampaignId($sellerId)
                    ->setProductId($data['entity_id'])
                    ->setPrice($data['promotionPrice'])
                    ->setStatus(0)
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
                    ->setProductId($data['entity_id'])
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

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
