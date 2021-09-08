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
class Paymentmethodisactive implements ObserverInterface
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
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data       $helper
     * @param \Magento\Checkout\Model\Session          $checkoutSession
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->response = $response;
    }
    
    /**
     * Payment method is active.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletProductId = $this->helper->getWalletProductId();
        $paymentMethods = $this->helper->getPaymentMethods();
        $paymentMethodArray = explode(',', $paymentMethods);
        $event = $observer->getEvent();
        $method = $event->getMethodInstance();
        $cardonly = false;
        $cartData = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($cartData as $item) {
            if ($item->getProduct()->getId() == $walletProductId) {
                $cardonly = true;
            }
        }
        if (!empty($paymentMethods)) {
            if (!in_array($method->getCode(), $paymentMethodArray) && $cardonly == true) {
                $result = $observer->getEvent()->getResult();
                $result->setData('is_available', false);
            }
        }

        if ($this->checkoutSession->getWalletDiscount()) {
            $getSession = $this->checkoutSession->getWalletDiscount();
            if (is_array($getSession)
                && array_key_exists('amount', $getSession)
                && array_key_exists('grand_total', $getSession)
            ) {
                if ($method->getCode()=="free") {
                    $checkResult = $event->getResult();
                    //this is disabling the payment method at checkout page
                    $checkResult->setData('is_available', false);
                }
            }
        }
    }
}
