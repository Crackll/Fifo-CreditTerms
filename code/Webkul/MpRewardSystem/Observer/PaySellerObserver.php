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
namespace Webkul\MpRewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class PaySellerObserver implements ObserverInterface
{
    /**
     * @var \Webkul\MpRewardSystem\Model\ResourceModel\Rewarddetail\CollectionFactory
     */
    protected $sellerCredits;
    /**
     * @param \Magento\Framework\Event\Manager                                                   $eventManager
     * @param \Webkul\MpRewardSystem\Model\ResourceModel\Rewarddetail\CollectionFactory $sellerRewarddetail
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Webkul\MpRewardSystem\Model\ResourceModel\Rewarddetail\CollectionFactory $sellerRewarddetail
    ) {
        $this->_sellerRewarddetail = $sellerRewarddetail;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getEvent()->getData();
        $sellerId = $data[0]['seller_id'];
        $orderId = $data[0]['id'];
        $rewardRecordCollection = $this->_sellerRewarddetail
            ->create()
            ->addFieldToFilter('seller_id', $sellerId)
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('action', ['eq' => 'debit'])
            ->addFieldToFilter('status', ['eq' => 0]);

        foreach ($rewardRecordCollection as $reward) {
            $reward->setStatus(1);
            $reward->save();
        }
    }
}
