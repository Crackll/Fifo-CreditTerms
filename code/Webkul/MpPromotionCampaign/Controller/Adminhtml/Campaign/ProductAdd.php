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

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;

class ProductAdd extends \Magento\Backend\App\Action
{
    /**
     * Marketplace Helper
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $marketplaceHelper;

    /**
     * Redirect
     *
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    public $redirect;

    /**
     * Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Product Collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $collectionFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Webkul\MpCampaign\Model\ProductFactory $productFactory
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Webkul\MpCampaign\Logger\Logger $logger
     * @param \Webkul\MpCampaign\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        Filter $filter,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
        CollectionFactory $collectionFactory,
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaign,
        \Magento\Catalog\Model\ProductFactory $product
    ) {
        $this->campaignProduct = $campaignProduct;
        $this->helper = $helper;
        $this->campaign = $campaign;
        $this->product = $product;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->redirect = $redirect;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Mass enable products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $selectedId = $this->getRequest()->getparam('selected');
        if (isset($selectedId)) {
            foreach ($selectedId as $id) {
                $productIds[] = $id;
            }
        } else {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $productIds = $collection->getAllIds();
        }
        $count = 0;
        $sellerId = $this->marketplaceHelper->getCustomerId();
        $url = $this->redirect->getRefererUrl();
        $urlData = $this->helper->getCampaignIdFromUrl($url);
        $campaignId = $urlData;
        foreach ($productIds as $productId) {
            $checkProduct = $this->campaignProduct->create()
                            ->getCollection()
                            ->addFieldToFilter('campaign_id', $campaignId)
                            ->addFieldToFilter('product_id', $productId)
                            ->getLastItem();
            $count++;
            // Get Campaign Details to save to product
            $campainDetail =  $this->campaign->create()
                        ->getCollection()
                        ->addFieldToFilter('entity_id', $campaignId)
                        ->getFirstItem()
                        ->getData();
            // Load product
            $productDetail = $this->product->create()->load($productId);
            $stockItem = $productDetail->getExtensionAttributes()->getStockItem();
            $productPrice = $productDetail->getPrice();
            $productQty = $productDetail->getQty();
            $promotionPrice = ceil($productPrice -($productPrice * $campainDetail['discount']/100));
            // Check if product entry is save to campaign
            if ($checkProduct->getId()) {
                $checkProduct->setStatus(CampaignProModel::STATUS_JOIN)->save();
            } else {
                // Save Product Entry to Campaign
                $checkProduct->setCampaignId($campaignId)
                    ->setSellerCampaignId(0)
                    ->setProductId($productId)
                    ->setPrice($promotionPrice)
                    ->setStatus(CampaignProModel::STATUS_JOIN)
                    ->setQty($stockItem->getQty())
                    ->save();
            }
            // Save Campaign Details to Product
            $this->helper->updateSpecialPriceDates(
                $productDetail->getSku(),
                $promotionPrice,
                date("Y-m-d h:i:s", strtotime($campainDetail['start_date'])),
                date("Y-m-d h:i:s", strtotime($campainDetail['end_date']))
            );
        }
        $this->messageManager->addSuccess(
            __(
                'A total of %1 product(s) have been added.',
                $count
            )
        );
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/product', ['id' => $campaignId]);
    }
}
