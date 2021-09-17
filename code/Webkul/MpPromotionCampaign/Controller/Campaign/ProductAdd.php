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

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;

class ProductAdd extends \Magento\Customer\Controller\AbstractAccount implements CsrfAwareActionInterface
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
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
    /**
     * Mass enable products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $selectedId = $this->getRequest()->getparam('selected');
        $sellerId = $this->marketplaceHelper->getCustomerId();
        if (isset($selectedId)) {
            foreach ($selectedId as $id) {
                $productIds[] = $id;
            }
        } else {
            //$collection = $this->filter->getCollection($this->collectionFactory->create());
            $collection = $this->collectionFactory->create()
                            ->addFieldToFilter('seller_id', $sellerId);
            $productIds = $collection->getAllIds();
        }
        $count = 0;
        $url = $this->redirect->getRefererUrl();
        $urlData = $this->helper->getCampaignIdFromUrl($url);
        $campaignId = $urlData;
        $campaignStatus = $this->helper->campaignStatus($campaignId);
        if ($campaignStatus['code'] == CampaignModel::CAMPAIGN_STATUS_COMMINGSOON) {
            foreach ($productIds as $productId) {
                $checkProduct = $this->campaignProduct->create()->getCollection()
                ->addFieldToFilter('campaign_id', $campaignId)
                ->addFieldToFilter('seller_campaign_id', $sellerId)
                ->addFieldToFilter('product_id', $productId)->getLastItem();
                if ($checkProduct->getId()) {
                    $count++;
                    $checkProduct->setStatus(CampaignProModel::STATUS_PENDING)
                    ->save();
                } else {
                    $count++;
                    $campainDetail =  $this->campaign->create()->getCollection()
                    ->addFieldToFilter('entity_id', $campaignId)
                    ->getFirstItem()
                    ->getData();
                    $productDetail = $this->product->create()->load($productId);
                    $stockItem= $productDetail->getExtensionAttributes()->getStockItem();
                    $productPrice = $productDetail->getPrice();
                    $productQty = $productDetail->getQty();
                    $promotionPrice = ceil($productPrice -($productPrice * $campainDetail['discount']/100));
                    $checkProduct->setCampaignId($campaignId)
                    ->setSellerCampaignId($sellerId)
                    ->setProductId($productId)
                    ->setPrice($promotionPrice)
                    ->setStatus(CampaignProModel::STATUS_PENDING)
                    ->setQty($stockItem->getQty())->save();
                }
            }
            $this->messageManager->addSuccess(
                __(
                    'A total of %1 product(s) have been added.',
                    $count
                )
            );
        } else {
            $this->messageManager->addError(__("Campaign already start. You can't add product"));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($url);
    }
}
