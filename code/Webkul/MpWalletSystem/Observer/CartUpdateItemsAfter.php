<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul MpWalletSystem Observer Class
 */
class CartUpdateItemsAfter implements ObserverInterface
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $messageManager;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Webkul\MpWalletSystem\Helper\Data          $helper
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletProductId = $this->helper->getWalletProductId();
        $cart = $observer->getCart()->getQuote()->getAllItems();
        foreach ($cart as $item) {
            $productId = $item->getProductId();
            if ($productId == $walletProductId) {
                $this->updateItemQty($item);
                $this->messageManager->addNotice(
                    __("You can not update wallet product's quantity.")
                );
            }
        }
    }

    /**
     * Update item Quantity function
     *
     * @param object $item
     */
    public function updateItemQty($item)
    {
        $item->setQty(1);
        if (!$this->helper->getDiscountEnable()) {
            $item->setNoDiscount(1);
        } else {
            $item->setNoDiscount(0);
        }
        $item->save();
    }
}
