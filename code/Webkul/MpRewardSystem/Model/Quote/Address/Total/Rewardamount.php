<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\SessionManager;

class Rewardamount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Discount calculation object
     *
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $calculator;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper
     * @param SessionManager $session
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        SessionManager $session
    ) {
        $this->setCode('mpreward');
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->_mpRewardHelper = $mpRewardHelper;
    }
    /**
     * collect the reward on product
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return void
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $shippingAssignment->getItems();

        if (empty($items)) {
            return $this;
        }
        $address = $shippingAssignment->getShipping()->getAddress();
        $rewardInfo = $this->session->getRewardInfo();
        $helper = $this->_mpRewardHelper;
        $rewardamountAmount = 0;
        $store = $quote->getStore();
        if (is_array($rewardInfo)) {
            foreach ($rewardInfo as $key => $info) {
                $rewardamountAmount = $rewardamountAmount + $info['amount'];
            }
        }
        $currentCurrencyCode = $helper->getCurrentCurrencyCode();
        $baseCurrencyCode = $helper->getBaseCurrencyCode();
        $allowedCurrencies = $helper->getConfigAllowCurrencies();
        $rates = $helper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }
        $baserewardamountAmount = $helper->baseCurrencyAmount($rewardamountAmount);
        $baserewardamountAmount = -($baserewardamountAmount);
        $rewardamountAmount = -($rewardamountAmount*$rates[$currentCurrencyCode]);
        $address->setData('mpreward_amount', $rewardamountAmount);
        $address->setData('base_mpreward_amount', $baserewardamountAmount);
        $total->setTotalAmount('mpreward', $rewardamountAmount);
        $total->setBaseTotalAmount('mpreward', $baserewardamountAmount);
        $quote->setMprewardAmount($rewardamountAmount);
        $quote->setBaseMprewardAmount($baserewardamountAmount);
        $total->setMprewardAmount($rewardamountAmount);
        $total->setBaseMprewardAmount($baserewardamountAmount);
        return $this;
    }

    /**
     * Add shipping totals information to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     *
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $title = __('Rewarded Amount');
        return [
            'code'  => $this->getCode(),
            'title' => $title,
            'value' => $total->getMprewardAmount()
        ];
    }

    /**
     * Get Shipping label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Rewarded Amount');
    }
}
