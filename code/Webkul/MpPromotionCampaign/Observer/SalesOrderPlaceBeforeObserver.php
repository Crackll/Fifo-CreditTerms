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
use Magento\Quote\Model\QuoteRepository;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;

/**
 * Webkul MpPromotionCampaign SalesOrderPlaceBeforeObserver Observer Model.
 */
class SalesOrderPlaceBeforeObserver implements ObserverInterface
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
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
        \Psr\Log\LoggerInterface $logger,
        QuoteRepository $quoteRepository,
        \Magento\Catalog\Model\ProductFactory $product
    ) {
        
        $this->_quoteRepository = $quoteRepository;
        $this->campaignProduct = $campaignProduct;
        $this->product = $product;
        $this->logger = $logger;
    }

    /**
     * Sales Order Place Before event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteId=$observer->getEvent()->getOrder()->getQuoteId();
        $quote =  $this->_quoteRepository->get($quoteId);
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            $productDetail =  $this->product->create()->load($item->getProductId());
            if ($productDetail->getCampaignId()) {
                $campaignProduct =  $this->campaignProduct->create()
                ->getCollection()
                ->addFieldToFilter('campaign_id', $productDetail->getCampaignId())
                ->addFieldToFilter('product_id', $item->getProductId())
                ->addFieldToFilter('status', CampaignProModel::STATUS_JOIN)->getFirstItem();
                if ($campaignProduct->getData() && ($campaignProduct->getQty() !== null)) {
                    $totalQty = $item->getqty() + $campaignProduct->getSoldQty();
                    if ($campaignProduct->getQty() <  $totalQty) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("You can't purchase  " .$item->getName())
                        );
                    }
                }
            }
        }
        return $this;
    }
}
