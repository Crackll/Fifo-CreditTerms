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

namespace Webkul\MpWalletSystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * Webkul MpWalletSystem Controller
 */
class Applypaymentamount extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var TotalsCollector
     */
    protected $collectTotal;

    /**
     * Initialize dependencies
     *
     * @param Context                            $context
     * @param \Webkul\MpWalletSystem\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session    $checkoutSession
     * @param \Magento\Customer\Model\Session    $customerSession
     * @param PageFactory                        $resultPageFactory
     * @param \Magento\Checkout\Helper\Cart      $cartHelper
     * @param TotalsCollector                    $collectTotal
     */
    public function __construct(
        Context $context,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Helper\Cart $cartHelper,
        TotalsCollector $collectTotal
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->cartHelper = $cartHelper;
        $this->collectTotal = $collectTotal;
    }

    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $customerId = $this->helper->getCustomerId();
        if (!$customerId) {
            $leftinWallet = $this->helper->getformattedPrice(0);
            $myValue = [
                'flag' => 0,
                'amount' => 0,
                'type' => $params['wallet'],
                'leftinWallet' => $leftinWallet,
            ];
            $this->checkoutSession->setWalletDiscount($myValue);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($myValue);
            return $resultJson;
        }
        $grandtotal = $this->checkoutSession->getQuote()->getGrandTotal();
        $grandtotal = (float) $grandtotal;
        $grandtotal = round($grandtotal, 4);
        $customerId = $this->helper->getCustomerId();
        $amount = $this->helper->getWalletTotalAmount($customerId);
        $store = $this->helper->getStore();
        $converttedAmount = $this->helper->currentCurrencyAmount($amount, $store);
        if ($params['wallet'] == 'set') {
            if ($converttedAmount >= $grandtotal) {
                $discountAmount = $grandtotal;
            } else {
                $discountAmount = $converttedAmount;
            }
            $left = $converttedAmount - $discountAmount;
            $baseLeftAmount = $this->helper->baseCurrencyAmount($left);
            $leftinWallet = $this->helper->getformattedPrice(
                ($baseLeftAmount) > 0 ? $baseLeftAmount : 0
            );
            $myValue = [
                'flag' => 1,
                'amount' => $discountAmount,
                'type' => $params['wallet'],
                'grand_total' => $grandtotal,
                'leftinWallet' => $leftinWallet,
            ];
            $this->checkoutSession->setWalletDiscount($myValue);
        } else {
            $leftinWallet = $this->helper->getformattedPrice($amount);
            $myValue = [
                'flag' => 0,
                'amount' => 0,
                'type' => $params['wallet'],
                'grand_total' => $grandtotal,
                'leftinWallet' => $leftinWallet,
            ];
            $this->checkoutSession->setWalletDiscount($myValue);
        }
        if ($this->helper->isEnabledMpsplitorder()) {
            $this->customerSession->setWalletDiscount($this->checkoutSession->getWalletDiscount());
        }
        $this->checkoutSession->getQuote()->collectTotals()->save();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($myValue);
        return $resultJson;
    }

    /**
     * Get final grand total
     *
     * @param object $quote
     * @return int
     */
    protected function getFinalGrandTotal($quote)
    {
        $grandTotal = 0;
        if (!empty($quote->getAllAddresses())) {
            foreach ($quote->getAllAddresses() as $address) {
                $addressTotal = $this->collectTotal->collectAddressTotals($quote, $address);
                $grandTotal = $grandTotal + $addressTotal->getGrandTotal();
            }
        }
        return $grandTotal;
    }
}
