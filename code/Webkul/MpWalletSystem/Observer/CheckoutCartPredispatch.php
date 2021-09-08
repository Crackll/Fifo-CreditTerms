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
use Magento\Quote\Model\Quote\ItemFactory;

/**
 * Webkul MpWalletSystem Observer Class
 */
class CheckoutCartPredispatch implements ObserverInterface
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;
    
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var itemFactory
     */
    protected $itemFactory;
    
    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data          $helper
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResponseInterface    $response
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayment,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ItemFactory $itemFactory
    ) {
        $this->helper = $helper;
        $this->splitPayment = $splitPayment;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->itemFactory = $itemFactory;
    }
    
    /**
     * Customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletProductId = $this->helper->getWalletProductId();
        $minimumAmount = $this->helper->getMinimumAmount();
        $maximumAmount = $this->helper->getMaximumAmount();
        $cartData = $this->checkoutSession->getQuote()->getAllItems();
        if ($minimumAmount > $maximumAmount) {
            $temp = $minimumAmount;
            $minimumAmount = $maximumAmount;
            $maximumAmount = $temp;
        }
        $currentCurrenyCode = $this->helper->getCurrentCurrencyCode();
        $baseCurrenyCode = $this->helper->getBaseCurrencyCode();

        $finalminimumAmount = $this->helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $minimumAmount
        );
        $finalmaximumAmount = $this->helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $maximumAmount
        );
        $amount = 0;
        $itemId = '';
        $itemIds = '';
        $price = '';
        $flag = 0;
        $walletItemId = 0;
        $walletInCart = 0;
        $otherInCart = 0;
        $currencySymbol = $this->helper->getCurrencySymbol(
            $this->helper->getCurrentCurrencyCode()
        );
        foreach ($cartData as $cart) {
            if ($cart->getProduct()->getId() == $walletProductId) {
                $walletInCart = 1;
                $walletItemId = $cart->getItemId();
                $amount = $cart->getCustomPrice();
                if ($amount > $finalmaximumAmount) {
                    $amount = $finalmaximumAmount;
                    $flag = 1;
                    $this->messageManager->addNotice(
                        __(
                            'You can not add more than %1 amount to your wallet.',
                            $currencySymbol.$finalmaximumAmount
                        )
                    );
                } elseif ($amount < $finalminimumAmount) {
                    $amount = $finalminimumAmount;
                    $flag = 1;
                    $this->messageManager->addNotice(
                        __(
                            'You can not add less than %1 amount to your wallet.',
                            $currencySymbol.$finalminimumAmount
                        )
                    );
                }
                if ($flag == 1) {
                    $this->updateCartData($cart, $amount);
                    $this->checkoutSession->getQuote()->setItemsQty(1);
                    $this->checkoutSession->getQuote()->setSubtotal($amount);
                    $this->checkoutSession->getQuote()->setGrandTotal($amount);
                    $storeManager = $this->storeManager;
                    $currentStore = $storeManager->getStore();
                    $url = $currentStore->getBaseUrl().'checkout/cart/';
                    $this->response->setRedirect($url)->sendResponse();
                } else {
                    if (!$this->helper->getDiscountEnable()) {
                        $cart->setNoDiscount(1);
                    } else {
                        $cart->setNoDiscount(0);
                    }
                    $this->splitPayment->commitMethod($cart);
                }
            } else {
                $otherInCart = 1;
            }
        }
        if ($walletInCart==1 && $otherInCart==1 && $walletItemId!=0) {
            $quote = $this->itemFactory->create()->load($walletItemId);
            $quote->delete();
        }
        $this->checkoutSession->getQuote()->save();
    }

    /**
     * Update Cart Data function
     *
     * @param object $cart
     * @param int $amount
     */
    public function updateCartData($cart, $amount)
    {
        $cart->setCustomPrice($amount);
        $cart->setOriginalCustomPrice($amount);
        $cart->setQty(1);
        $cart->getProduct()->setIsSuperMode(true);
        if (!$this->helper->getDiscountEnable()) {
            $cart->setNoDiscount(1);
        } else {
            $cart->setNoDiscount(0);
        }
        $cart->setRowTotal($amount);
        $cart->save();
    }
}
