<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Webkul\MpRewardSystem\Helper\Data
     */
    protected $rewardSystemHelper;

    /**
     * @var \Webkul\MpRewardSystem\Model\RewarddetailFactory
     */
    protected $rewardDetailFactory;

    /**
     * @param \Webkul\MpRewardSystem\Helper\Data                $rewardSystemHelper
     * @param \Webkul\MpRewardSystem\Model\RewarddetailFactory  $rewardDetailFactory
     */

    public function __construct(
        \Webkul\MpRewardSystem\Helper\Data $rewardSystemHelper,
        \Webkul\MpRewardSystem\Model\RewarddetailFactory $rewardDetailFactory
    ) {
        $this->rewardSystemHelper = $rewardSystemHelper;
        $this->rewardDetailFactory = $rewardDetailFactory;
    }

    /**
     * Invoice save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->rewardSystemHelper;
        $order = $observer->getEvent()->getCreditmemo()->getOrder();
        $incrementId = $order->getIncrementId();
        $rewardPoint = 0;
        $rewardModel = $this->rewardDetailFactory->create()->getCollection()
                     ->addFieldToFilter('order_id', $order->getId())
                     ->addFieldToFilter('action', ['eq' =>'credit'])
                     ->addFieldToFilter('customer_id', $order->getCustomerId())
                     ->addFieldToFilter('status', 1)
                     ->addFieldToFilter('is_revert', 0);
        foreach ($rewardModel as $rewardsData) {
          /*Save refund reward amount Data in Marketplace Seller Order*/
            $sellerid = $rewardsData->getSellerId();
            $status = $this->checkRewardRevertStatus($order, $rewardsData);
            $rewardPoint = $rewardsData->getRewardPoint();
            if ($status && $rewardPoint) {
                $transactionNote = __('Order id : %1 Debited amount on order item cancel', $incrementId);
                $rewardData = [
                'customer_id' => $order->getCustomerId(),
                'seller_id' => $sellerid,
                'points' => $rewardPoint,
                'type' => 'debit',
                'review_id' => 0,
                'is_revert' => 1,
                'order_id' => $order->getId(),
                'status' => 1,
                'note' => $transactionNote,
                'pending' => $rewardsData->getPendingReward()
                ];
                $msg = __(
                    'Revert %1 reward points on order #%2 item cancel',
                    $rewardPoint,
                    $incrementId
                );
                $adminMsg = __(
                    ' Revert %1 reward points on order #%2 item cancel',
                    $rewardPoint,
                    $incrementId
                );
                $rewardsData->setIsRevert(1)->save();
                $helper->setDataFromAdmin($msg, $adminMsg, $rewardData);
            }
        }
    }
    /**
     * Check the reward status
     * @return status
     */
    public function checkRewardRevertStatus($order, $rewardData)
    {
        $status = false;
        $items = $order->getAllVisibleItems();
        $itemIds = $rewardData->getItemIds();
        $itemIdsArray = explode(',', $itemIds);
        foreach ($items as $item) {
            if (in_array($item->getId(), $itemIdsArray)) {
                if ($item->getQtyCanceled()) {
                    $status = true;
                }
            }
        }
        return $status;
    }
}
