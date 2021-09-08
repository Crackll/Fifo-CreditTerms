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

namespace Webkul\MpWalletSystem\Model\Total;

/**
 * Webkul MpWalletSystem Model Class
 */
class Quotetotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    protected $walletHelper;
    
    /**
     * @var Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Model\Context   $context
     * @param \Magento\Checkout\Model\Session    $checkoutsession
     * @param \Magento\Customer\Model\Session    $customerSession
     * @param \Webkul\MpWalletSystem\Helper\Data $walletHelper
     * @param \Magento\Checkout\Helper\Cart      $cartHelper
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Magento\Checkout\Helper\Cart $cartHelper
    ) {
        $this->setCode('wallet');
        $this->checkoutSession = $checkoutsession;
        $this->customerSession = $customerSession;
        $this->walletHelper = $walletHelper;
        $this->cartHelper = $cartHelper;
    }

    /**
     * Collect totals process.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }
        $helper = $this->walletHelper;
        $helper->checkWalletproductWithOtherProduct();
        $address = $shippingAssignment->getShipping()->getAddress();
        $totalsArray = $total->getAllTotalAmounts();
        $grandTotal = array_sum($totalsArray);
        $balance = ($this->walletHelper->isEnabledMpsplitorder())?
        $this->getWalletamountForMpSplitorder($grandTotal, $totalsArray):
        $this->getWalletamountForCart($grandTotal, $totalsArray);
        if ($balance) {
            $currentCurrencyCode = $helper->getCurrentCurrencyCode();
            $baseCurrencyCode = $helper->getBaseCurrencyCode();
            $allowedCurrencies = $helper->getConfigAllowCurrencies();
            $rates = $helper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
            if (empty($rates[$currentCurrencyCode])) {
                $rates[$currentCurrencyCode] = 1;
            }
            $baseAmount = $helper->baseCurrencyAmount($balance);
            $balance = -($balance);
            $baseAmount = -($baseAmount);
            $address->setData('wallet_amount', $balance);
            $address->setData('base_wallet_amount', $baseAmount);
            $total->setTotalAmount('wallet', $balance);
            $total->setBaseTotalAmount('wallet', $baseAmount);
            $quote->setWalletAmount($balance);
            $quote->setBaseWalletAmount($baseAmount);
            $total->setWalletAmount($balance);
            $total->setBaseWalletAmount($baseAmount);
        } else {
            $address->setData('wallet_amount', 0);
            $address->setData('base_wallet_amount', 0);
            $total->setTotalAmount('wallet', 0);
            $total->setBaseTotalAmount('wallet', 0);
            $quote->setWalletAmount(0);
            $quote->setBaseWalletAmount(0);
            $total->setWalletAmount(0);
            $total->setBaseWalletAmount(0);
        }
        return $this;
    }

    /**
     * Get Wallet amount For Cart function
     *
     * @param int $addressGrandTotal
     * @param array $totalsArray
     */
    protected function getWalletamountForCart($addressGrandTotal, $totalsArray)
    {
        $getSession = $this->checkoutSession->getWalletDiscount();
        $wallethelper = $this->walletHelper;
        $cartHelper = $this->cartHelper;
        $amount = 0;
        $finalWalletAmount = 0;
        $grandtotal = 0;
        if (is_array($getSession) && array_key_exists('flag', $getSession) && $getSession['flag'] == 1) {
            $totalByToatls = 0;
            foreach ($totalsArray as $akey => $totalIns) {
                if (!in_array($akey, ['wallet_amount'])) {
                    $totalByToatls += $totalIns;
                }
            }
            $grandtotal = round($totalByToatls, 4);
            if ($getSession['grand_total'] != $grandtotal) {
                $getSession['grand_total'] = $grandtotal;
                $getSession['amount'] = 0;
                $getSession['type'] = 'reset';
                $this->checkoutSession->setWalletDiscount($getSession);
                return 0;
            }
            $amount = $getSession['amount'];
            $finalWalletAmount = $getSession['amount'];
            if ($getSession['type'] == 'set') {
                $customerId = $wallethelper->getCustomerId();
                $totalAmount = $wallethelper->currentCurrencyAmount(
                    $wallethelper->getWalletTotalAmount($customerId),
                    ''
                );
                if ($getSession['amount'] > $grandtotal) {
                    $amount = $grandtotal;
                }
                if ($amount < $grandtotal) {
                    if ($grandtotal < $totalAmount) {
                        $amount = $grandtotal;
                    } else {
                        $amount = $totalAmount;
                    }
                }
                $walletPercent = ($amount*100)/$grandtotal;
                $finalWalletAmount = ($addressGrandTotal*$walletPercent)/100;
            }
        }

        return $finalWalletAmount;
    }

    /**
     * Get Wallet amount For MpSplitOrder module
     *
     * @param int $addressGrandTotal
     * @param array $totalsArray
     */
    protected function getWalletamountForMpSplitorder($addressGrandTotal, $totalsArray)
    {
        $getSession = $this->customerSession->getWalletDiscount();
        $grandTotalOfOriginQuote = $getSession["grand_total"];
        $wallethelper = $this->walletHelper;
        $cartHelper = $this->cartHelper;
        $amount = 0;
        $finalWalletAmount = 0;
        $grandtotal = 0;
        if (is_array($getSession) && array_key_exists('flag', $getSession) && $getSession['flag'] == 1) {
            $totalByToatls = 0;
            foreach ($totalsArray as $akey => $totalIns) {
                if (!in_array($akey, ['wallet_amount'])) {
                    $totalByToatls += $totalIns;
                }
            }
            $grandtotal = round($totalByToatls, 4);
            $finalWalletAmount = round($getSession['amount']*($grandtotal/$grandTotalOfOriginQuote));
            $getSession["grand_total"] = $grandtotal;
            $getSession["amount"] = $finalWalletAmount;
            $this->checkoutSession->setWalletDiscount($getSession);
        }

        return $finalWalletAmount;
    }

    /**
     * Fetch (Retrieve data as array)
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @internal param \Magento\Quote\Model\Quote\Address $address
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        return [
            'code' => $this->getCode(),
            'title' => __('Wallet Amount'),
            'value' => $total->getWalletAmount(),
        ];
    }

    /**
     * Label getter
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Wallet Amount');
    }
}
