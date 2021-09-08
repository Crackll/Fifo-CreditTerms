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
namespace Webkul\MpRewardSystem\Controller\Checkout;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManager;

class ApplyRewards extends Action
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    /**
     * @var \Webkul\MpRewardSystem\Model\RewardrecordFactory
     */
    protected $rewardRecord;
    /**
     * @var \Webkul\MpRewardSystem\Helper\Data
     */
    protected $mpRewardHelper;
    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $optionFactory;
    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductModel;
    /**
     * @var CustomerFactory
     */
    protected $customer;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    /**
     * Undocumented function
     *
     * @param Context $context
     * @param SessionManager $session
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MpRewardSystem\Model\RewardrecordFactory $rewardRecord
     * @param \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $optionFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductModel
     * @param CustomerFactory $customer
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        Context $context,
        SessionManager $session,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpRewardSystem\Model\RewardrecordFactory $rewardRecord,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        \Magento\Quote\Model\Quote\Item\OptionFactory $optionFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductModel,
        CustomerFactory $customer,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->session = $session;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->mpHelper = $mpHelper;
        $this->rewardRecord = $rewardRecord;
        $this->mpRewardHelper = $mpRewardHelper;
        $this->optionFactory = $optionFactory;
        $this->mpProductModel = $mpProductModel;
        $this->customer = $customer;
        $this->cart = $cart;
        $this->jsonHelper = $jsonHelper;
        $this->moduleManager = $moduleManager;
        parent::__construct($context);
    }
    /**
     * apply rewards
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $fieldValues = $this->getRequest()->getParams();
        $quote = $this->checkoutSession->getQuote();
        $totalRewards = $this->sellersRewards();

        if ($fieldValues['used_reward_points'] > $totalRewards[$fieldValues['seller_id']]['remaining_reward_point']) {
            $this->messageManager->addError('Reward points can\'t be greater than Seller\'s Reward Point. ');
            return $this->resultRedirectFactory
                ->create()
                ->setPath('checkout/cart', ['_secure' => $this->getRequest()->isSecure()]);
        }
        $rewardInfo = $this->session->getRewardInfo();
        $appiledPoints = 0;
        if ($rewardInfo) {
            foreach ($rewardInfo as $appliedRewardInfo) {
                $appiledPoints += $appliedRewardInfo['used_reward_points'];
            }
        }
        $maxRewardUsed = $this->mpRewardHelper->getRewardCanUsed();
        $appiledPoints += $fieldValues['used_reward_points'];
        if ($appiledPoints > $maxRewardUsed) {
            $message = 'You can not use more than ' . $maxRewardUsed . ' reward points for this order purchase.';
            $this->messageManager->addError($message);
            return $this->resultRedirectFactory
                ->create()
                ->setPath('checkout/cart', ['_secure' => $this->getRequest()->isSecure()]);
        }
        $sellerData = $this->getSellerData($quote);
        $sellerTotal = $sellerData[$fieldValues['seller_id']]['total'];
        $rewardAmount = $this->mpRewardHelper
            ->getRewardValue($fieldValues['seller_id']) * $fieldValues['used_reward_points'];
        if ($sellerTotal >= $rewardAmount) {
            $flag = 0;
            $amount = 0;
            if (!$rewardInfo) {
                $rewardInfo = [];
                $availAmount = $this->mpRewardHelper
                    ->getRewardValue($fieldValues['seller_id']) * $fieldValues['remaining_reward_point'];
                $amount = $rewardAmount;
                $rewardInfo[$fieldValues['seller_id']] =
                    [
                    'seller_id' => $fieldValues['seller_id'],
                    'used_reward_points' => $fieldValues['used_reward_points'],
                    'remaining_reward_point' => $fieldValues['remaining_reward_point'],
                    'avail_amount' => $availAmount,
                    'amount' => $amount,
                    'seller_name' => $this->getSellerName($fieldValues['seller_id']),
                    ];
            } else {
                foreach ($rewardInfo as $key => $info) {
                    if ($key == $fieldValues['seller_id']) {
                        $rewardInfo[$key]['used_reward_points'] = $fieldValues['used_reward_points'];
                        $rewardInfo[$key]['remaining_reward_point'] = $fieldValues['remaining_reward_point'];
                        $amount = $rewardAmount;
                        $rewardInfo[$key]['amount'] = $amount;

                        $flag = 1;
                    }
                }
                if ($flag == 0) {
                    $availAmount = $this->mpRewardHelper
                        ->getRewardValue($fieldValues['seller_id']) * $fieldValues['remaining_reward_point'];
                    $amount = $rewardAmount;
                    $rewardInfo[$fieldValues['seller_id']] = [
                        'seller_id' => $fieldValues['seller_id'],
                        'used_reward_points' => $fieldValues['used_reward_points'],
                        'remaining_reward_point' => $fieldValues['remaining_reward_point'],
                        'avail_amount' => $availAmount,
                        'amount' => $amount,
                        'seller_name' => $this->getSellerName($fieldValues['seller_id']),
                    ];
                }
            }
            $this->session->unsRewardInfo();
            // code implemented to test the compatibility with the seller coupons
            $rewardInfo = $this->sellerCouponCompatible($sellerTotal, $rewardInfo, $fieldValues);
            $this->session->setRewardInfo($rewardInfo);
            $this->messageManager->addSuccess('Reward has been applied successfully.');
        } else {
            $this->messageManager->addNotice('Reward Amount can not be greater than seller\'s total product amount.');
        }

        return $this->resultRedirectFactory
            ->create()
            ->setPath('checkout/cart', ['_secure' => $this->getRequest()->isSecure()]);
    }
    /**
     * @param Magento\Checkout\Model\Session $quote
     *
     * @return Mixed
     */
    public function getSellerData($quote)
    {
        $sellerData = [];
        $qty = 1;
        foreach ($quote->getAllItems() as $item) {
            $sellerId = 0;
            $options = $item->getProductOptions();
            if ($item->getQtyOrdered()) {
                $qty = $item->getQtyOrdered();
            } else {
                $qty = $item->getQty();
            }
            $mpassignproductId = 0;
            $itemOption = $this->optionFactory->create()
                ->getCollection()
                ->addFieldToFilter('item_id', $item->getId())
                ->addFieldToFilter('code', 'info_buyRequest');
            $optionValue = '';
            if (is_array($itemOption) && !empty($itemOption)) {
                foreach ($itemOption as $value) {
                    $optionValue = $value->getValue();
                }
            }
            if ($optionValue != '') {
                $temp = $this->jsonHelper->jsonDecode($optionValue);
                $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
            }
            if ($mpassignproductId) {
                $mpassignModel = $this->_objectManager
                    ->create(\Webkul\MpAssignProduct\Model\Items::class)
                    ->load($mpassignproductId);
                $sellerId = $mpassignModel->getSellerId();
            } else {
                $collectionProduct = $this->mpProductModel->create()->getCollection()
                    ->addFieldToFilter('mageproduct_id', ['eq' => $item->getProductId()]);
                foreach ($collectionProduct as $sellerid) {
                    $sellerId = $sellerid->getSellerId();
                }
            }
            if (!isset($sellerData[$sellerId]['details'])) {
                $sellerData[$sellerId]['details'] = [];
                $sellerData[$sellerId]['total'] = 0;
            }
            array_push(
                $sellerData[$sellerId]['details'],
                [
                    'product_id' => $item->getProductId(),
                    'price' => $item->getPrice() * $qty,
                ]
            );
            $sellerData[$sellerId]['total'] += $item->getPrice() * $qty;
        }

        return $sellerData;
    }
    /**
     * @return seller name
     */
    public function getSellerName($sellerId = 0)
    {
        if ($sellerId) {
            return $this->customer->create()->load($sellerId)->getName();
        } else {
            return "Admin";
        }
    }
    /**
     * seller rewards
     *
     * @return array
     */
    private function sellersRewards()
    {

        $customerId = $this->customerSession->getId();
        $quote = $this->checkoutSession->getQuote();
        $sellerIds = [];
        foreach ($quote->getAllItems() as $item) {
            $productId = $item->getProduct()->getId();
            $model = $this->mpHelper->getSellerProductDataByProductId($productId);
            if ($model->getSize()) {
                foreach ($model as $value) {
                    $sellerIds[] = $value->getSellerId();
                }
            } else {
                $sellerIds[] = 0;
            }
        }
        $options = [];
        $collection = $this->rewardRecord->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId])
            ->addFieldToFilter('seller_id', ['in' => $sellerIds]);
        foreach ($collection as $info) {
            if (!isset($options[$info->getSellerId()])) {
                $options[$info->getSellerId()] = [];
                $remainingRewards = $info->getRemainingRewardPoint();
            } else {
                $remainingRewards = $options[$info->getSellerId()]['remaining_reward_point']
                 + $info->getRemainingRewardPoint();
            }
            $options[$info->getSellerId()]['remaining_reward_point'] = $remainingRewards;
            $options[$info->getSellerId()]['amount'] = $remainingRewards
             * $this->mpRewardHelper->getRewardValue($info->getSellerId());
        }
        return $options;
    }
    /**
     * compatibility with seller coupon
     *
     * @param $sellerTotal
     * @param [] $rewardInfo
     * @param [] $fieldValues
     * @return array
     */
    public function sellerCouponCompatible($sellerTotal, $rewardInfo, $fieldValues)
    {
        if ($this->moduleManager->isOutputEnabled("Webkul_MpSellerCoupons")) {
            $couponArray = [];
            $couponArrayKey = [];
            $leftRewardAmount = 0;
            if ($this->cart->getQuote()->getCouponCode() != null) {
                $couponCodes = $this->cart->getQuote()->getCouponCode();
                $couponArray = $this->getSellerDataFromCouponCode($couponCodes);
                foreach ($couponArray as $key => $value) {
                    $couponArrayKey[$key] = $key;
                }
                if (isset($couponArrayKey[$fieldValues['seller_id']])) {
                    $sellerDiscount = $couponArray[$fieldValues['seller_id']]["amount"] * (-1);
                    $total = ($sellerTotal >= $sellerDiscount) ? $sellerTotal - $sellerDiscount : 0;
                    if ($total == 0) {
                        $rewardInfo[$fieldValues['seller_id']]["used_reward_points"] = 0;
                        $rewardInfo[$fieldValues['seller_id']]["amount"] = 0;
                    } else {
                        $extraTotalAmount = $total;
                        $amountPerPoints = $rewardInfo[$fieldValues['seller_id']]["amount"]
                            / $rewardInfo[$fieldValues['seller_id']]["used_reward_points"];
                        if ($rewardInfo[$fieldValues['seller_id']]["amount"] > $extraTotalAmount) {
                            $leftRewardAmount = $rewardInfo[$fieldValues['seller_id']]["amount"]
                                 - $extraTotalAmount;
                            $appliedRewardReduced = floor(($rewardInfo[$fieldValues['seller_id']]["amount"]
                                 - $leftRewardAmount) / $amountPerPoints);
                            $rewardInfo[$fieldValues['seller_id']]["used_reward_points"] = $appliedRewardReduced;
                            $rewardInfo[$fieldValues['seller_id']]["amount"] = $appliedRewardReduced
                                 * $amountPerPoints;
                        }
                    }
                }
            }
        }
        return $rewardInfo;
    }
    /**
     * Undocumented function
     *
     * @param [type] $couponCode
     * @return void
     */
    public function getSellerDataFromCouponCode($couponCode)
    {
        $couponInfo = [];
        $sellerCouponCodes = explode(",", $couponCode);
        foreach ($sellerCouponCodes as $key => $value) {
            # code...
            $mpSellerCouponsModel = $this->mpRewardHelper->getCouponInstance();
            $couponModel = $mpSellerCouponsModel->create()->load($value, 'coupon_code');
            $couponSellerId = $couponModel->getSellerId() ?? 0;
            $couponInfo[$couponSellerId] = [
                    'entity_id'=>$couponModel->getEntityId(),
                    'seller_id'=>$couponSellerId,
                    'coupon_code'=>$couponModel->getCouponCode(),
                    'amount'=>-$couponModel->getCouponValue(),
                    'notified' => 0,
                    "expire_at"=>$couponModel->getExpireAt(),
                    'currency_code' => $this->cart->getQuote()->getBaseCurrencyCode()
                ];
            $couponStatus = $couponModel->getStatus();
        }
        return $couponInfo;
    }
}
