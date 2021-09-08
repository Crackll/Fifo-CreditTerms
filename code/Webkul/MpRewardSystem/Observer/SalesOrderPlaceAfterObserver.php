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
use Magento\Framework\Session\SessionManager;
use Magento\Sales\Model\OrderFactory;
use Webkul\MpRewardSystem\Helper\Data as RewardSystemHelper;
use Webkul\MpRewardSystem\Model\RewarddetailFactory;
use Webkul\MpRewardSystem\Model\RewardrecordFactory;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
     /**
      * @var Session
      */
    protected $session;
    /**
     * @var eventManager
     */
    protected $eventManager;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var RewardrecordFactory
     */
    protected $rewardrecordFactory;
    /**
     * @var RewardSystemHelper
     */
    protected $rewardSystemHelper;
    /**
     * @var RewarddetailFactory
     */
    protected $rewardDetail;
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $orderModel;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    /**
     * @var \Webkul\MpRewardSystem\Logger\Logger
     */
    protected $logger;
    /**
     * @param OrderFactory                                $orderModel
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param  RewardSystemHelper                         $rewardSystemHelper
     * @param \Webkul\Marketplace\Model\OrdersFactory     $mpOrdersModel
     * @param \Webkul\Marketplace\Model\SaleslistFactory  $mpSalesListFactory
     * @param RewarddetailFactory                         $rewardDetail
     * @param SessionManager                              $session
     * @param \Webkul\MpRewardSystem\Logger\Logger        $logger
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        OrderFactory $orderModel,
        RewardSystemHelper $rewardSystemHelper,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrdersModel,
        \Webkul\Marketplace\Model\SaleslistFactory $mpSalesListFactory,
        RewarddetailFactory $rewardDetail,
        SessionManager $session,
        \Webkul\MpRewardSystem\Logger\Logger $logger
    ) {
        $this->session = $session;
        $this->date = $date;
        $this->orderModel = $orderModel;
        $this->rewardSystemHelper = $rewardSystemHelper;
        $this->_mpOrdersModel = $mpOrdersModel;
        $this->_mpSalesListFactory = $mpSalesListFactory;
        $this->rewardDetail = $rewardDetail;
        $this->logger = $logger;
    }
    /**
     * customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->rewardSystemHelper;
        $enableRewardSystem = $helper->getMpRewardEnabled();
        $order = $observer->getOrder();
        if ($order) {
            $customerId = $order->getCustomerId();
            
            if ($enableRewardSystem && $customerId) {
                $currencyCode = $order->getOrderCurrencyCode();
                $orderId = $order->getId();
                if ($this->alreadyAddedInData($orderId)) {
                    return;
                }
                $rewardInfo = $this->session->getRewardInfo();
                $incrementId = $this->orderModel
                    ->create()
                    ->load($orderId)
                    ->getIncrementId();
                if (is_array($rewardInfo)) {
                    $this->deductRewardPointFromCustomer(
                        $customerId,
                        $incrementId,
                        $orderId,
                        $rewardInfo
                    );
                }
                $this->addCreditAmountData($orderId, $customerId, $incrementId);
                $this->session->unsRewardInfo();
            }
        } else {
            $orders = $order ? [$order] : $observer->getOrders();
            foreach ($orders as $order) {
                $customerId = $order->getCustomerId();
                if ($enableRewardSystem && $customerId) {
                    $currencyCode = $order->getOrderCurrencyCode();
                    $orderId = $order->getId();
                    if ($this->alreadyAddedInData($orderId)) {
                        return;
                    }
                    $rewardInfo = $this->session->getRewardInfo();
                    $incrementId = $this->orderModel
                        ->create()
                        ->load($orderId)
                        ->getIncrementId();
                    if (is_array($rewardInfo)) {
                        $this->deductRewardPointFromCustomer(
                            $customerId,
                            $incrementId,
                            $orderId,
                            $rewardInfo
                        );
                    }
                    $this->addCreditAmountData($orderId, $customerId, $incrementId);
                    $this->session->unsRewardInfo();
                }
            }
        }
    }
    /**
     * add the reward amount to the customer credit
     * @param $orderId
     * @param $customerId
     * @param $incrementId
     */
    public function addCreditAmountData($orderId, $customerId, $incrementId)
    {
        $helper = $this->rewardSystemHelper;
        $rewardPointDetails = $helper->calculateCreditAmountforOrder($orderId);
        if (!empty($rewardPointDetails)) {
            foreach ($rewardPointDetails as $sellerId => $rewardPointData) {
                $rewardPoint = isset($rewardPointData['points']) ? $rewardPointData['points'] : 0;
                $itemIds = $rewardPointData['item_ids'];
                $rewardType = $rewardPointData['reward_type'];
                if ($rewardPoint > 0) {
                    $transactionNote = __('Order id : %1 credited amount', $incrementId);
                    $rewardData = [
                        'customer_id' => $customerId,
                        'seller_id' => $sellerId,
                        'points' => $rewardPoint,
                        'type' => 'credit',
                        'review_id' => 0,
                        'order_id' => $orderId,
                        'item_ids' => $itemIds,
                        'reward_type' => $rewardType,
                        'status' => 0,
                        'note' => $transactionNote,
                        'pending' => 0,
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
                    $helper->setDataFromAdmin(
                        $msg,
                        $adminMsg,
                        $rewardData
                    );
                }
            }
        }
    }
    /**
     * Deduct reward point from the customer
     * @param $customerId
     * @param $incrementId
     * @param $orderId
     * @param $rewardInfo
     */
    public function deductRewardPointFromCustomer(
        $customerId,
        $incrementId,
        $orderId,
        $rewardInfo
    ) {
        foreach ($rewardInfo as $key => $info) {
            $helper = $this->rewardSystemHelper;
            $transactionNote = __('Order id : %1 debited amount', $incrementId);
            $rewardPoints = $info['used_reward_points'];
            $rewardData = [
                'customer_id' => $customerId,
                'seller_id' => isset($info['seller_id']) ? $info['seller_id'] : 0,
                'points' => $rewardPoints,
                'type' => 'debit',
                'review_id' => 0,
                'order_id' => $orderId,
                'status' => 1,
                'note' => $transactionNote,
                'pending' => 0,
            ];
            $msg = __(
                'You used %1 reward points on order #%2',
                $rewardPoints,
                $incrementId
            );
            $adminMsg = __(
                ' have placed an order on your site, and used %1 reward points',
                $rewardPoints
            );
            $helper->setDataFromAdmin(
                $msg,
                $adminMsg,
                $rewardData
            );
            /*Save Data in Marketplace Seller Order*/
            $rewardAmount = $info['amount'];
            $baseRewardAmount = $helper->baseCurrencyAmount($rewardAmount);
            $sellerid = isset($info['seller_id']) ? $info['seller_id'] : 0;
            $mpOrderCollection = $this->_mpOrdersModel->create()
                ->getCollection()
                ->addFieldToFilter('seller_id', $sellerid)
                ->addFieldToFilter('order_id', $orderId);
            foreach ($mpOrderCollection as $mporder) {
                $mporder->setRewardAmount($baseRewardAmount)->save();
            }
            /*Save Data in Marketplace Sales List*/
            $mpSalesCollection = $this->_mpSalesListFactory->create()
                ->getCollection()
                ->addFieldToFilter('seller_id', $sellerid)
                ->addFieldToFilter('order_id', $orderId);
            foreach ($mpSalesCollection as $mpsales) {
                if ($baseRewardAmount * 1 != 0) {
                    if ($mpsales->getActualSellerAmount() >= $baseRewardAmount) {
                        $appliedRewardAmount = $baseRewardAmount;
                        $baseRewardAmount = 0;
                    } else {
                        $appliedRewardAmount = $mpsales->getActualSellerAmount();
                        $baseRewardAmount = $baseRewardAmount - $mpsales->getActualSellerAmount();
                    }
                    $mpsales->setAppliedRewardAmount($appliedRewardAmount);
                    $mpsales->save();
                }
            }
        }
    }
    /**
     * check already added the reward to order
     * @param $orderId
     * @return TRUE/False
     */
    public function alreadyAddedInData($orderId)
    {
        $rewardDetailCollection = $this->rewardDetail
            ->create()
            ->getCollection()
            ->addFieldToFilter('order_id', ['eq' => $orderId]);

        if ($rewardDetailCollection->getSize()) {
            return true;
        }
        return false;
    }
}
