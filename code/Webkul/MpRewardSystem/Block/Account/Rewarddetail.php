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
namespace Webkul\MpRewardSystem\Block\Account;

use Webkul\MpRewardSystem\Model\RewardrecordFactory;
use Webkul\MpRewardSystem\Model\RewarddetailFactory;
use Magento\Customer\Model\CustomerFactory;
use Webkul\Marketplace\Block\Product\ProductlistFactory;
use Magento\Sales\Model\Order;

class Rewarddetail extends \Magento\Framework\View\Element\Template
{
    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $customer;
    /**
     * @var Webkul\MpRewardSystem\Model\RewardrecordFactory
     */
    protected $rewardRecords;
    /**
     * @var Webkul\MpRewardSystem\Model\RewarddetailFactory
     */
    protected $rewardDetails;

    protected $rewards;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @param Context $context
     * @param Order  $order
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        RewardrecordFactory $rewardRecords,
        CustomerFactory $customer,
        Order $order,
        RewarddetailFactory $rewardDetails,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->customer = $customer;
        $this->rewardRecords = $rewardRecords;
        $this->order = $order;
        $this->rewardDetails = $rewardDetails;
        parent::__construct($context, $data);
    }
    /**
     * reward Record Collection
     * @return Webkul\MpRewardSystem\Model\Rewardrecord
     */
    public function getAllCollection()
    {
        $customerId = $this->customerSession->getId();
        $this->rewards =  $this->rewardDetails
                ->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq'=>$customerId]);
        return $this->rewards;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpreward.details.list.pager'
            )->setCollection(
                $this->getAllCollection()
            );
            $this->setChild('pager', $pager);
            $this->getAllCollection()->load();
        }
        return $this;
    }
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * current customer
     */
    public function _getCustomerData()
    {
        return $this->customerSession->getCustomer();
    }
    /**
     *
     * @return array $details
     */
    public function getRewardDetails()
    {
        $customerId = $this->customerSession->getId();
        $collection = $this->rewardRecords->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq'=>$customerId]);
        $details = [];
        $totalCredits = 0;
        $totalDebits = 0;
        $totalRemaining = 0;
        $pendingReward=0;
        foreach ($collection as $row) {
            $totalCredits = $totalCredits + $row->getTotalRewardPoint();
            $totalDebits = $totalDebits + $row->getUsedRewardPoint();
            $totalRemaining = $totalRemaining + $row->getRemainingRewardPoint();
        }
        $details['total_credits'] =  $totalCredits;
        $details['total_debits'] =  $totalDebits;
        $details['total_remaining'] =  $totalRemaining;
        $rewardDetailsData = $this->getAllCollection();
        foreach ($rewardDetailsData as $row) {
            $pendingReward += $row->getPendingReward();
        }
        $details['pending_reward'] =  $pendingReward;
        return $details;
    }

    public function getOrder()
    {
        return $this->order;
    }
}
