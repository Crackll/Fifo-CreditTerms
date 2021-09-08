<?php
/**
 * Webkul MpAuction ControllerActionPredispatchCheckoutCartAdd Observer Model.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\SessionFactory as CustomerSession;

class ControllerActionPredispatchCheckoutCartAdd implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Webkul\MpAuction\Model\ProductFactory      $auctionProductFactory
     */
    public function __construct(
        ProductFactory $product,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory,
        \Webkul\MpAuction\Helper\Data $helper,
        CustomerSession $customerSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->product = $product;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->customerSession = $customerSession->create();
    }

    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getControllerAction();
        $cartItems = $this->checkoutSession->getQuote()->getAllItems();
        $cartItemsCount = count($cartItems);
        $proId = $observer->getRequest()->getParam('product');
        $auctionOpt = $this->product->create()->load($proId)->getAuctionType();
        $auctionOpt = explode(',', $auctionOpt);
        $auctionProductData = $this->auctionProductFactory->create()->getCollection()
            ->addFieldToFilter('product_id', ['eq' => $proId])
            ->addFieldToFilter('auction_status', ['in' => [1,0]])->setPageSize(1)->getLastItem();
        $data = $observer->getRequest()->getParams();
        if (!in_array(1, $auctionOpt) && $auctionProductData->getEntityId()) {
            $auctionProductDataTemp = $this->auctionProductFactory->create()
                                                    ->getCollection()
                                                    ->addFieldToFilter('product_id', ['eq' => $proId])
                                                    ->addFieldToFilter('auction_status', ['in' => [0]])
                                                    ->setPageSize(1)->getLastItem();
            $tempId = $auctionProductDataTemp ? $auctionProductDataTemp->getId() : 0;
            $winnerBidDetail = $this->helper->getWinnerBidDetail($tempId);
            if (!($winnerBidDetail && $this->customerSession->getCustomerId() == $winnerBidDetail->getCustomerId())) {
                $this->messageManager->addError(__('You can not add auction product to cart.'));
                $observer->getRequest()->setParam('product', false);
                return $this;
            }
        }
        $qty = 0;
        $maxQty = $auctionProductData->getMaxQty();
        $nonAuctionProduct = false;
        foreach ($cartItems as $item) {
            $auctionProduct = $this->auctionProductFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['eq' => $item->getProductId()])
                    ->addFieldToFilter('status', ['eq' => 0])->setPageSize(1)->getLastItem();
            if ($auctionProduct->getEntityId() && $item->getProductId() != $proId) {
                $msg = 'You can not add another product with auction Product.';
                $this->messageManager->addError(__($msg));
                $observer->getRequest()->setParam('product', false);
                return $this;
            }
            if ($item->getProductId() == $proId) {
                $qty = $qty + $item->getQty();
            } else {
                $nonAuctionProduct = true;
            }
        }
        if ($auctionProductData->getEntityId()) {
            if ($nonAuctionProduct) {
                $msg = 'You can not add auction Product with another product.';
                $this->messageManager->addError(__($msg));
                $observer->getRequest()->setParam('product', false);
                return $this;
            }
            if ($qty == $maxQty) {
                $msg = 'Maximum '. $maxQty.' quantities are allowed to purchase this item.';
                $this->messageManager->addNotice(__($msg));
                $observer->getRequest()->setParam('product', false);
                return $this;
            } elseif ($qty < $maxQty && $observer->getRequest()->getParam('qty') > $maxQty-$qty) {
                $msg = 'Maximum '. $maxQty.' quantities are allowed to purchase this item.';
                $this->messageManager->addNotice(__($msg));
                $observer->getRequest()->setParam('qty', ($maxQty-$qty));
                return $this;
            } elseif (($qty + $observer->getRequest()->getParam('qty'))<$auctionProductData->getMinQty()) {
                $msg = 'Minimum '. $auctionProductData->getMinQty() .' quantities are allowed to purchase this item.';
                $this->messageManager->addNotice(__($msg));
                $observer->getRequest()->setParam('product', false);
                return $this;
            }
        }
        return $this;
    }

    /**
     * getFirstItemFromRecord
     * @param Collection Object
     * @return false | data
     */
    private function getFirstItemFromRecord($collection)
    {
        foreach ($collection as $row) {
            return $row;
        }
        return false;
    }
}
