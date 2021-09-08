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
namespace Webkul\MpRewardSystem\Controller\Account;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\MpRewardSystem\Model\RewarddetailFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpRewardSystem\Model\RewardrecordFactory;

class Sellerrewarddetails extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Webkul\MpRewardSystem\Model\RewardrecordFactory
     */
    protected $rewardRecords;
    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * Undocumented function
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RewardrecordFactory $rewardRecords
     * @param CustomerFactory $customerModel
     * @param RewarddetailFactory $rewardDetails
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Customer\Model\Url $url
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RewardrecordFactory $rewardRecords,
        CustomerFactory $customerModel,
        RewarddetailFactory $rewardDetails,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Url $url
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->rewardRecords = $rewardRecords;
        $this->jsonHelper = $jsonHelper;
        $this->customerModel = $customerModel;
        $this->url = $url;
        $this->rewardDetails = $rewardDetails;
        parent::__construct($context);
    }
    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }
    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $wholedata = $this->getRequest()->getParams();
        $customerId = $this->customerSession->getCustomer()->getId();
        $pendingReward =  $this->rewardDetails
                ->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq'=>$customerId]);
        $collection = $this->rewardRecords->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $data = [];
        if ($wholedata['type'] == 'credit') {
            foreach ($collection as $row) {
                $sellerId = $row->getSellerId();
                $sellerName = "Admin";
                if ($sellerId) {
                    $sellerName = $this->customerModel->create()->load($sellerId)->getFirstname();
                }
                array_push(
                    $data,
                    [
                        'seller_id' => $sellerId,
                        'seller' => $sellerName,
                        'total' => ($row->getTotalRewardPoint()),
                    ]
                );
            }
        } elseif ($wholedata['type'] == 'debit') {
            foreach ($collection as $row) {
                $sellerId = $row->getSellerId();
                $sellerName = "Admin";
                if ($sellerId) {
                    $sellerName = $this->customerModel->create()->load($sellerId)->getFirstname();
                }
                array_push(
                    $data,
                    [
                        'seller_id' => $sellerId,
                        'seller' => $sellerName,
                        'used' => ($row->getUsedRewardPoint()),
                    ]
                );
            }
        } elseif ($wholedata['type'] == 'remaining_amount') {
            foreach ($collection as $row) {
                $sellerId = $row->getSellerId();
                $sellerName = "Admin";
                if ($sellerId) {
                    $sellerName = $this->customerModel->create()->load($sellerId)->getFirstname();
                }
                array_push(
                    $data,
                    [
                        'seller_id' => $sellerId,
                        'seller' => $sellerName,
                        'total' => ($row->getTotalRewardPoint()),
                        'used' => ($row->getUsedRewardPoint()),
                        'remaining' => ($row->getRemainingRewardPoint()),
                    ]
                );
            }
        } elseif ($wholedata['type'] == 'pending_amount') {
            $pendingRewards = [];
            $getTotalRewardPoint = 0;
            $getUsedRewardPoint = 0;
            foreach ($pendingReward as $row) {
                $sellerId = $row->getSellerId();
                $sellerName = "Admin";
                if ($sellerId) {
                    $sellerName = $this->customerModel->create()->load($sellerId)->getFirstname();
                }
                if ($row->getPendingReward() > 0) {
                    if (!isset($pendingRewards[$sellerName]["pending"])) {
                        $pendingRewards[$sellerName]["pending"] = 0;
                    }
                    $pendingRewards[$sellerName] = [
                        'seller_id' => $sellerId,
                        'seller' => $sellerName,
                        'total' => $row->getTotalRewardPoint(),
                        'used' => $row->getUsedRewardPoint(),
                        'pending' => ($pendingRewards[$sellerName]["pending"] + $row->getPendingReward())
                    ];
                }
                
            }
            if ($pendingRewards != null) {
                foreach ($pendingRewards as $key => $value) {
                    # code...
                    array_push(
                        $data,
                        $value
                    );
                }
            } else {
                array_push(
                    $data,
                    [
                        'seller_id' => $sellerId,
                        'seller' => $sellerName,
                        'total' => "",
                        'used' => "",
                        'pending' => 0
                    ]
                );
            }
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($data));
    }
}
