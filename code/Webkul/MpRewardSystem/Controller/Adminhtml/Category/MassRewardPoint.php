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

namespace Webkul\MpRewardSystem\Controller\Adminhtml\Category;

use Webkul\MpRewardSystem\Controller\Adminhtml\Category as CategoryController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Webkul\MpRewardSystem\Model\RewardcategoryFactory;
use Webkul\MpRewardSystem\Api\RewardcategoryRepositoryInterface;
use Webkul\MpRewardSystem\Api\Data\RewardcategoryInterfaceFactory;

class MassRewardPoint extends CategoryController
{
    /**
     * @var Webkul\MpRewardSystem\Model\RewardproductFactory
     */
    protected $rewardCategory;
    /**
     * @var Webkul\MpRewardSystem\Helper\Data
     */
    protected $mpRewardHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
     /**
      * @var \Magento\Framework\Json\DecoderInterface
      */
    protected $jsonDecoder;
    /**
     * @param Action\Context $context
     * @param RewardcategoryFactory $rewardCategory
     * @param \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        Action\Context $context,
        RewardcategoryFactory $rewardCategory,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->rewardCategory = $rewardCategory;
        $this->mpRewardHelper = $mpRewardHelper;
        $this->jsonDecoder = $jsonDecoder;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $successCounter = 0;
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (array_key_exists('reward_category', $params) && $params['reward_category'] != '') {
            if (array_key_exists('rewardpoint', $params) && array_key_exists('status', $params)) {
                $categoryIds = array_flip($this->jsonDecoder->decode($params['reward_category']));
                $coditionArr = [];
                foreach ($categoryIds as $categoryId) {
                    $rewardCategoryModel = $this->rewardCategory->create()->load($categoryId, 'category_id');
                    if ($rewardCategoryModel->getEntityId()) {
                        $rewardPoint = $params['rewardpoint'];
                        if ($params['rewardpoint'] == '') {
                            $rewardPoint = $rewardCategoryModel->getPoints();
                        }
                        $data = [
                            'category_id' => $rewardCategoryModel->getCategoryId(),
                            'points' => $rewardPoint,
                            'seller_id' => 0,
                            'status' => $params['status'],
                            'entity_id' => $rewardCategoryModel->getEntityId()
                        ];
                    } else {
                        $data = [
                            'category_id' => $categoryId,
                            'seller_id' => 0,
                            'points' => $params['rewardpoint'],
                            'status' => $params['status']
                        ];
                    }
                    $this->mpRewardHelper->setCategoryRewardData($data);
                }
                if ($params['rewardpoint'] == '') {
                    $this->messageManager->addSuccess(
                        __("Total of %1 category(s) reward status are updated", count($categoryIds))
                    );
                } else {
                    $this->messageManager->addSuccess(
                        __("Total of %1 category(s) reward are updated", count($categoryIds))
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Please Enter a valid points number to add.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Please select categoryies to set points.')
            );
        }
        return $resultRedirect->setPath('mprewardsystem/category/index');
    }
}
