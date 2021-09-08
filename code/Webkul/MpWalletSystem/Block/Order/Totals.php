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

namespace Webkul\MpWalletSystem\Block\Order\Totals;

use Magento\Sales\Model\Order;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;

/**
 * Webkul MpWalletSystem Block
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    protected $orderCollection;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data                  $helper
     * @param Collection                                       $orderCollection
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $helper,
        CollectionFactory $orderCollection,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->orderCollection = $orderCollection;
        parent::__construct($context, $data);
    }

    /**
     * Initialize seller's order totals relates with rewardamount
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->order = $parent->getOrder();
        $this->source = $parent->getSource();
        $source = $this->source;
        if (isset($source[0])) {
            $source = $source[0];
            $currencyRate = $source['currency_rate'];
            $appliedRewardAmount = 0;
            $sellerListCollection = $this->orderCollection->create()
                ->addFieldToFilter(
                    'main_table.order_id',
                    $this->getOrder()->getId()
                )->addFieldToFilter(
                    'main_table.seller_id',
                    $this->helper->getCustomerId()
                );
            foreach ($sellerListCollection as $sellerData) {
                $appliedRewardAmount += $sellerData->getAppliedRewardAmount();
            }
            if ($appliedRewardAmount) {
                $this->addRewardAmount($currencyRate, (-$appliedRewardAmount));
                $this->_initOrderedTotal($currencyRate);
                $this->_initVendorTotal($currencyRate);
                if ($this->order->isCurrencyDifferent()) {
                    $this->_initBaseOrderedTotal($currencyRate);
                    $this->_initBaseVendorTotal($currencyRate);
                }
            }
        }
        return $this;
    }

    /**
     * Add applied reward amount string
     *
     * @param string $currencyRate
     * @param string $appliedRewardAmount
     * @param string $after
     */
    protected function addRewardAmount($currencyRate, $appliedRewardAmount, $after = 'discount')
    {
        $rewardTotal = new \Magento\Framework\DataObject(
            [
                'code' => 'rewardamount',
                'base_value' => $appliedRewardAmount,
                'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $appliedRewardAmount),
                'label' => __('Rewarded Amount')
            ]
        );
        $this->getParentBlock()->addTotal($rewardTotal, $after);
        return $this;
    }

    /**
     * Initialize Ordered Total function
     *
     * @return $this
     */
    protected function _initOrderedTotal()
    {
        $parent = $this->getParentBlock();
        $reward = $parent->getTotal('rewardamount');
        $total = $parent->getTotal('ordered_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $reward->getValue());
        return $this;
    }

    /**
     * Initialize Base Ordered Total
     *
     * @return $this
     */
    protected function _initBaseOrderedTotal()
    {
        $parent = $this->getParentBlock();
        $reward = $parent->getTotal('rewardamount');
        $total = $parent->getTotal('base_ordered_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $reward->getBaseValue());
        return $this;
    }

    /**
     * Initialize Vendor Total
     *
     * @return $this
     */
    protected function _initVendorTotal()
    {
        $parent = $this->getParentBlock();
        $reward = $parent->getTotal('rewardamount');
        $total = $parent->getTotal('vendor_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $reward->getValue());
        return $this;
    }

    /**
     * Initialize Base Vendor Total
     *
     * @return $this
     */
    protected function _initBaseVendorTotal()
    {
        $parent = $this->getParentBlock();
        $reward = $parent->getTotal('rewardamount');
        $total = $parent->getTotal('base_vendor_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $reward->getBaseValue());
        return $this;
    }

    /**
     * Get Order function
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
