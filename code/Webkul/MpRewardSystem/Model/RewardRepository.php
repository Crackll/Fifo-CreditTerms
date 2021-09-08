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
namespace Webkul\MpRewardSystem\Model;

use Magento\Framework\Session\SessionManager;

class RewardRepository implements \Webkul\MpRewardSystem\Api\RewardRepositoryInterface
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Webkul\MpRewardSystem\Helper\Data
     */
    protected $mpRewardHelper;
    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $options;
    /**
     * @var\Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductModel;
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
     * @param SessionManager $session
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $options
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductModel
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        SessionManager $session,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        \Magento\Quote\Model\Quote\Item\OptionFactory $options,
        \Webkul\Marketplace\Model\ProductFactory $mpProductModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->session = $session;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->mpRewardHelper = $mpRewardHelper;
        $this->objectManager = $objectManager;
        $this->options = $options;
        $this->mpProductModel = $mpProductModel;
        $this->jsonHelper = $jsonHelper;
        $this->cart = $cart;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Save reward.
     *
     * @param \Webkul\MpRewardSystem\Api\RewardRepositoryInterface $rewardParams
     *
     * @return array $rewardinfo
     *
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     */
    public function save($rewardParams = [])
    {
        $fieldValues = $this->request->getParams();
        if (isset($fieldValues['cancel'])) {
            $this->session->unsRewardInfo();
            return [];
        }
        $rewardInfo = $this->session->getRewardInfo();
        $appiledPoints = 0;
        if ($rewardInfo) {
            foreach ($rewardInfo as $appliedRewardInfo) {
                $appiledPoints += $appliedRewardInfo['used_reward_points'];
            }
        }
        $quote = $this->checkoutSession->getQuote();

        $totalRewards = $this->mpRewardHelper->getSellerRewards();
        $maxRewardUsed = $this->mpRewardHelper->getRewardCanUsed();
        if ($fieldValues['used_reward_points'] > $totalRewards[$fieldValues['seller_id']]['remaining_reward_point']) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Reward points can\'t be greater than Seller\'s Reward Point.')
            );
        }
        $appiledPoints += $fieldValues['used_reward_points'];
        if ($appiledPoints > $maxRewardUsed) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can not use more than %1 reward points for this order purchase.', $maxRewardUsed)
            );
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

                $rewardInfo[$fieldValues['seller_id']] = [
                    'seller_id' => $fieldValues['seller_id'],
                    'used_reward_points' => $fieldValues['used_reward_points'],
                    'remaining_reward_point' => $fieldValues['remaining_reward_point'],
                    'avail_amount' => $availAmount,
                    'amount' => $amount,
                    'seller_name' => $this->mpRewardHelper->getSellerName($fieldValues['seller_id']),
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
                        'seller_name' => $this->mpRewardHelper->getSellerName($fieldValues['seller_id']),
                    ];
                }
            }
            /** code implemented to test the compatibility with the seller coupons */
            $rewardInfo = $this->sellerCouponCompatible($sellerTotal, $rewardInfo, $fieldValues);
            $this->session->setRewardInfo($rewardInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Reward Amount can not be greater then total seller amount.')
            );
        }

        return $rewardInfo;
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
            $itemOption = $this->options->create()
                ->getCollection()
                ->addFieldToFilter('item_id', $item->getId())
                ->addFieldToFilter('code', 'info_buyRequest');
            $optionValue = '';
            if ($itemOption->getSize() > 0) {
                foreach ($itemOption as $value) {
                    $optionValue = $value->getValue();
                }
            }
            if ($optionValue != '') {
                $temp = $this->jsonHelper->jsonDecode($optionValue);
                $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
            }
            if ($mpassignproductId) {
                $mpassignModel = $this->objectManager
                    ->create(\Webkul\MpAssignProduct\Model\Items::class)
                    ->load($mpassignproductId);
                $sellerId = $mpassignModel->getSellerId();
            } else {
                $collectionProduct = $this->mpProductModel->create()->getCollection();
                $collectionProduct->addFieldToFilter('mageproduct_id', ['eq' => $item->getProductId()]);
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
                'entity_id' => $couponModel->getEntityId(),
                'seller_id' => $couponSellerId,
                'coupon_code' => $couponModel->getCouponCode(),
                'amount' => -$couponModel->getCouponValue(),
                'notified' => 0,
                "expire_at" => $couponModel->getExpireAt(),
                'currency_code' => $this->cart->getQuote()->getBaseCurrencyCode(),
            ];
            $couponStatus = $couponModel->getStatus();
        }
        return $couponInfo;
    }
}
