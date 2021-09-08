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
use Webkul\MpRewardSystem\Helper\Data as MpRewardSystemHelper;
use Webkul\MpRewardSystem\Api\Data\RewardrecordInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\MpRewardSystem\Api\RewardrecordRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var MpRewardSystemHelper
     */
    protected $mpRewardSystemHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var RewardrecordInterfaceFactory
     */
    protected $rewardRecordInterface;
    /**
     * @var DateTime
     */
    protected $date;
    /**
     * @var RewardrecordRepositoryInterface
     */
    protected $rewardRecordRepository;
    /**
     * @param MpRewardSystemHelper            $mpRewardSystemHelper
     * @param DataObjectHelper                $dataObjectHelper
     * @param RewardrecordInterfaceFactory    $rewardRecordInterface
     * @param ManagerInterface                $messageManager
     * @param RewardrecordRepositoryInterface $rewardRecordRepository
     * @param DateTime                        $datetime
     */
    public function __construct(
        MpRewardSystemHelper $mpRewardSystemHelper,
        DataObjectHelper $dataObjectHelper,
        RewardrecordInterfaceFactory $rewardRecordInterface,
        ManagerInterface $messageManager,
        RewardrecordRepositoryInterface $rewardRecordRepository,
        DateTime $datetime
    ) {
        $this->mpRewardSystemHelper = $mpRewardSystemHelper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->rewardRecordInterface = $rewardRecordInterface;
        $this->messageManager = $messageManager;
        $this->rewardRecordRepository = $rewardRecordRepository;
        $this->date = $datetime;
    }
    /**
     * cart save after observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->mpRewardSystemHelper;
        $enableRewardSystem = $helper->getMpRewardEnabled();
        if ($helper->getAllowRegistration() && $enableRewardSystem) {
            if ($helper->getRewardOnRegistration()) {
                $customer = $observer->getCustomer();
                $customerId = $customer->getId();
                $transactionNote = __("Reward point on registration");
                $rewardValue = $helper->getRewardValue();
                $rewardPoints = $helper->getRewardOnRegistration();
                $rewardData = [
                    'customer_id' => $customerId,
                    'seller_id' => 0,
                    'points' => $rewardPoints,
                    'type' => 'credit',
                    'review_id' => 0,
                    'order_id' => 0,
                    'status' => 1,
                    'note' => $transactionNote,
                    'pending' => 0,
                    'register'=>1
                ];
                $msg = __(
                    'You got %1 reward points on registration',
                    $rewardPoints
                );
                $adminMsg = __(
                    ' have registered on your site, and got %1 reward points',
                    $rewardPoints
                );
                $helper->setDataFromAdmin(
                    $msg,
                    $adminMsg,
                    $rewardData
                );
                $this->messageManager->addSuccess(__(
                    'You got %1 reward points on registration',
                    $rewardPoints
                ));
            }
        }
    }
}
