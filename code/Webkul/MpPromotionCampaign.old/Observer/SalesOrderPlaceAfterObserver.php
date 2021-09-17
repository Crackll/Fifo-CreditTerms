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

namespace Webkul\MpPromotionCampaign\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;

/**
 * Webkul MpPromotionCampaign SalesOrderPlaceAfterObserver Observer Model.
 */
class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var logger
     */
    protected $logger;

    /**
     * @var campaignProduct
     */
    protected $campaignProduct;

    /**
     * @var product
     */
    protected $product;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $product,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->campaignProduct = $campaignProduct;
        $this->product = $product;
        $this->logger = $logger;
    }

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $order_id = $order->getIncrementId();
        foreach ($order->getAllItems() as $item) {
            $productDetail =  $this->product->create()->load($item->getProductId());
            if ($productDetail->getCampaignId()) {
                 $campaignProduct =  $this->campaignProduct->create()
                 ->getCollection()
                 ->addFieldToFilter('campaign_id', $productDetail->getCampaignId())
                 ->addFieldToFilter('product_id', $item->getProductId())
                 ->addFieldToFilter('status', CampaignProModel::STATUS_JOIN)->getFirstItem();
                if ($campaignProduct->getData() && ($campaignProduct->getQty() !== null)) {
                    $totalQty = $item->getQtyOrdered() + $campaignProduct->getSoldQty();
                    if ($campaignProduct->getQty() >= $totalQty) {
                        $campaignProduct->setSoldQty($totalQty);
                        $campaignProduct->save();
                        if ($totalQty == $campaignProduct->getQty()) {
                            $product = $this->productRepository->getById($campaignProduct->getProductId());
                            $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                            $product->setSpecialFromDate(date("m/d/Y", strtotime('-1 day')));
                            $product->save();

                        }
                    }
                }
            }
        }
    }
}
