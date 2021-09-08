<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Block\Sales\Order;

use Magento\Sales\Model\Order;

class Reward extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;
     /**
      * @return source
      */
    public function getSource()
    {
        return $this->source;
    }

    public function displayFullSummary()
    {
        return true;
    }
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->order = $parent->getOrder();
        $this->source = $parent->getSource();
        $title = 'Rewarded Amount';
        $store = $this->getStore();
        if ($this->order->getMprewardAmount()!=0) {
            $rewardamount = new \Magento\Framework\DataObject(
                [
                    'code' => 'rewardamount',
                    'strong' => false,
                    'value' => $this->order->getMprewardAmount(),
                    'label' => __($title),
                ]
            );
            $parent->addTotal($rewardamount, 'rewardamount');
        }
        return $this;
    }

    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
