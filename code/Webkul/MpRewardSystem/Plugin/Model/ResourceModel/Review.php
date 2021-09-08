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

namespace Webkul\MpRewardSystem\Plugin\Model\ResourceModel;

use Webkul\MpRewardSystem\Helper\Data as MpRewardSystemHelper;

class Review
{
    /**
     * @var \Webkul\MpRewardSystem\Helper\Data
     */
    protected $mpRewardSystemHelper;
    /**
     * @var Webkul\MpRewardSystem\Model\RewarddetailFactory
     */
    protected $rewardDetailFactory;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;
    /**
     * @var Magento\Catalog\Model\ProductFactory
     */
    protected $catalogFactory;

    /**
     * @param \Webkul\MpRewardSystem\Helper\Data               $mpRewardSystemHelper
     * @param \Magento\Framework\App\Request\Http              $request
     * @param \Magento\Review\Model\ReviewFactory              $reviewFactory
     * @param \Magento\Catalog\Model\ProductFactory            $catalogFactory
     * @param \Webkul\MpRewardSystem\Model\RewarddetailFactory $rewardDetailFactory
     */
    public function __construct(
        MpRewardSystemHelper $mpRewardSystemHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $catalogFactory,
        \Webkul\MpRewardSystem\Model\RewarddetailFactory $rewardDetailFactory
    ) {
        $this->mpRewardSystemHelper = $mpRewardSystemHelper;
        $this->request = $request;
        $this->reviewFactory = $reviewFactory;
        $this->catalogFactory = $catalogFactory;
        $this->rewardDetailFactory = $rewardDetailFactory;
    }
    /**
     * @return $result
     */
    public function afterAggregate(
        \Magento\Review\Model\ResourceModel\Review $subject,
        $result
    ) {
        $helper = $this->mpRewardSystemHelper;
        $enableRewardSystem = $helper->getMpRewardEnabled();
        if ($helper->getAllowReview() && $enableRewardSystem) {
            $params = $this->request->getParams();
            if (array_key_exists('id', $params)) {
                $reviewId = $params['id'];
                $this->updateReviewForReviewId($reviewId);
            } elseif (array_key_exists('reviews', $params)) {
                foreach ($params['reviews'] as $key => $reviewId) {
                    $this->updateReviewForReviewId($reviewId);
                }
            }
        }
        return $result;
    }
    /**
     * check thwe review id
     * @return TRUE/False
     */
    public function checkReviewId($reviewId)
    {
        $reviewDetailCollection = $this->rewardDetailFactory->create()
            ->getCollection()
            ->addFieldToFilter('review_id', ['eq'=>$reviewId]);
        if ($reviewDetailCollection->getSize()) {
            return false;
        }
        return true;
    }
    /**
     * update the review for the product according to review id
     */
    public function updateReviewForReviewId($reviewId)
    {
        $helper = $this->mpRewardSystemHelper;
        $reviewStatus = $this->checkReviewId($reviewId);
        if ($reviewStatus) {
            $reviewModel = $this->reviewFactory->create()->load($reviewId);
            $productId = $reviewModel->getEntityPkValue();
            $product = $this->catalogFactory->create()->load($productId);
            $customerId = $reviewModel->getCustomerId();
            if ($reviewModel->getStatusId() == \Magento\Review\Model\Review::STATUS_APPROVED && $customerId) {
                $rewardPoints = $helper->getRewardOnReview();
                $transactionNote = __("Reward Point for product review on %1", $product->getName());
                $rewardData = [
                    'customer_id' => $customerId,
                    'seller_id' => 0,
                    'points' => $rewardPoints,
                    'type' => 'credit',
                    'review_id' => $reviewModel->getReviewId(),
                    'order_id' => 0,
                    'status' => 1,
                    'note' => $transactionNote
                ];
                $msg = __(
                    'You got %1 reward points for product review',
                    $rewardPoints
                );
                $adminMsg = __(
                    "'s product review get approved, and got %1 reward points",
                    $rewardPoints
                );
                $this->mpRewardSystemHelper->setDataFromAdmin(
                    $msg,
                    $adminMsg,
                    $rewardData
                );
            }
        }
    }
}
