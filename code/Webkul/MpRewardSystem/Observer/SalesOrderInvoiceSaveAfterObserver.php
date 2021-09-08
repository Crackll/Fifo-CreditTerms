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

class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
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
     * @var Webkul\MpRewardSystem\Logger\Logger
     */
    protected $logger;
    /**
     * @var Webkul\MpRewardSystem\Helper\Data
     */
    protected $data;
    /**
     * @var Webkul\MpRewardSystem\Block\Account\Rewarddetail
     */
    protected $rewardDetails;

    /**
     * @param \Webkul\MpRewardSystem\Helper\Data                $rewardSystemHelper
     * @param \Webkul\MpRewardSystem\Model\RewarddetailFactory  $rewardDetailFactory
     * @param \Webkul\MpRewardSystem\Logger\Logger              $logger
     * @param \Webkul\MpRewardSystem\Helper\Data                $data
     * @param \Webkul\MpRewardSystem\Block\Account\Rewarddetail $rewardDetails
     */
    public function __construct(
        \Webkul\MpRewardSystem\Helper\Data $rewardSystemHelper,
        \Webkul\MpRewardSystem\Model\RewarddetailFactory $rewardDetailFactory,
        \Webkul\MpRewardSystem\Logger\Logger $logger,
        \Webkul\MpRewardSystem\Helper\Data $data,
        \Webkul\MpRewardSystem\Block\Account\Rewarddetail $rewardDetails
    ) {
        $this->rewardSystemHelper = $rewardSystemHelper;
        $this->rewardDetailFactory = $rewardDetailFactory;
        $this->logger=$logger;
        $this->data=$data;
        $this->rewardDetails=$rewardDetails;
    }

    /**
     * Invoice save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->rewardSystemHelper;
        $order = $observer->getOrder();
        $incrementId = $order->getIncrementId();//this is the order id
        $rewardPoint = 0;
        $rewardId = 0;
        $rewardModel = $this->rewardDetailFactory->create()->getCollection()
                     ->addFieldToFilter('order_id', $order->getId())
                     ->addFieldToFilter('action', ['eq' =>'credit'])
                     ->addFieldToFilter('customer_id', $order->getCustomerId())
                     ->addFieldToFilter('status', 0);
        foreach ($rewardModel as $rewardData) {
            $status = $this->checkRewardGetStatus($order, $rewardData);
            $rewardPoint = $rewardData->getRewardPoint();
            $rewardId = $rewardData->getId();
            if ($status && $rewardPoint) {
                $transactionNote = __('Order id : %1 credited amount', $incrementId);

                $rewardData = [
                'customer_id' => $order->getCustomerId(),
                'seller_id' => $rewardData->getSellerId(),
                'points' => $rewardPoint,
                'type' => 'credit',
                'review_id' => 0,
                'order_id' => $order->getId(),
                'status' => 1,
                'note' => $transactionNote
                ];
                  $msg = __(
                      'You got %1 reward points on order #%2',
                      $rewardPoint,
                      $incrementId
                  );
                $adminMsg = __(
                    ' have placed an order on your site, and got %1 reward points',
                    $rewardPoint
                );
                $helper->updateRewardRecordData($msg, $adminMsg, $rewardData);
                $data = ['status' => 1];
                $rewardDetailModel = $this->rewardDetailFactory
                   ->create()
                   ->load($rewardId)
                   ->setStatus(1)
                   ->setPendingReward(0)->save();
            }
        }
    }
    public function checkRewardGetStatus($order, $rewardData)
    {
        $status = false;
        $items = $order->getAllVisibleItems();
        $itemIds = $rewardData->getItemIds();
        $itemIdsArray = explode(',', $itemIds);
        $this->logger->info("ITEMS IDS :", $itemIdsArray);
        foreach ($items as $item) {
            if (in_array($item->getId(), $itemIdsArray)) {
                $key = array_search($item->getId(), $itemIdsArray);
                unset($itemIdsArray[$key]);
                if ($item->getQtyOrdered() == $item->getQtyInvoiced()) {
                    $status = true;
                } else {
                    $status = false;
                    break;
                }
            }
        }
        if ($status) {
            if (!empty($itemIdsArray)) {
                $status = false;
            }
        }
        return $status;
    }
}
