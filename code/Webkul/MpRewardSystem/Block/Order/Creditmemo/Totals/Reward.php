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
namespace Webkul\MpRewardSystem\Block\Order\Creditmemo\Totals;

class Reward extends \Webkul\MpRewardSystem\Block\Order\Totals\Reward
{
    /**
     * Add applied reward amount string
     *
     * @param string $currencyRate
     * @param string $appliedRewardAmount
     * @param string $after
     */
    protected function _addRewardAmount($currencyRate, $appliedRewardAmount, $after = 'discount')
    {
        $parent = $this->getParentBlock();
        $creditmemoId = $parent->getCreditmemo()->getId();
        $rewardAmountData = $this->orderCollection->create()
        ->addFieldToFilter(
            'main_table.order_id',
            $this->getOrder()->getId()
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        );
        $salesCreditmemoItem = $this->orderCollection->create()->getTable('sales_creditmemo_item');
        $rewardAmountData->getSelect()->join(
            $salesCreditmemoItem.' as creditmemo_item',
            'creditmemo_item.order_item_id = main_table.order_item_id'
        )->where('creditmemo_item.parent_id = '.$creditmemoId);
        $rewardAmountTotal = 0;
        foreach ($rewardAmountData as $rewardData) {
            $rewardAmountTotal += $rewardData->getAppliedRewardAmount();
        }
        if ($rewardAmountTotal) {
            $rewardAmountTotal = -$rewardAmountTotal;
            $rewardTotal = new \Magento\Framework\DataObject(
                [
                  'code' => 'rewardamount',
                  'base_value' => $rewardAmountTotal,
                  'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $rewardAmountTotal),
                  'label' => __('Rewarded Amount')
                ]
            );
            $this->getParentBlock()->addTotal($rewardTotal, $after);
        }
        return $this;
    }
}
