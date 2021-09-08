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

class CampaignProductModified extends \Magento\Customer\Controller\AbstractAccount
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

            $campaignDetail = $this->campaignProduct->create()->load($data['id']);
            $campaignData = $this->campaign->create()->load($campaignDetail->getCampaignId());
           
            $campaignId = $this->getRequest()->getParam('id');
            $url = $this->redirect->getRefererUrl();
            $urlData = $this->helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
            $productPrice = $this->product->create()
            ->load($campaignDetail->getProductId())->getPrice();
        
            $promotionPrice = ceil($productPrice -($productPrice * $campaignData->getDiscount()/100));

            if ($data['price'] <= $promotionPrice) {
                $status = 1;
                if ($campaignDetail->getStatus() == 0) {
                    $status = 0;
                }
                $campaignDetail->setCampaignId($campaignId)
                ->setSellerCampaignId($sellerId)
                ->setProductId($campaignDetail->getProductId())
                ->setPrice($data['price'])
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
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
