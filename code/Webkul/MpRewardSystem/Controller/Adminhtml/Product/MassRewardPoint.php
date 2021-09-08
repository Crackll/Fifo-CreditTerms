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

namespace Webkul\MpRewardSystem\Controller\Adminhtml\Product;

use Webkul\MpRewardSystem\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Webkul\MpRewardSystem\Model\RewardproductFactory;

class MassRewardPoint extends ProductController
{
    /**
     * @var Webkul\MpRewardSystem\Model\RewardproductFactory
     */
    protected $rewardProduct;
    /**
     * @var Webkul\MpRewardSystem\Helper\Data
     */
    protected $mpRewardHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $mailHelper;
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;
    /**
     * @param Action\Context $context
     * @param RewardproductFactory $rewardProduct
     * @param \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        Action\Context $context,
        RewardproductFactory $rewardProduct,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->rewardProduct = $rewardProduct;
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
        if (array_key_exists('wkproductids', $params) && $params['wkproductids'] != '') {
            if (array_key_exists('rewardpoint', $params) &&
                array_key_exists('status', $params)) {
                $rewardProductCollection = $this->rewardProduct->create()->getCollection();
                $productIds = array_flip($this->jsonDecoder->decode($params['wkproductids']));
                $coditionArr = [];
                foreach ($productIds as $productId) {
                    $rewardProductModel = $this->rewardProduct->create()->load($productId, 'product_id');
                    if ($rewardProductModel->getEntityId()) {
                        $rewardPoint = $params['rewardpoint'];
                        if ($params['rewardpoint'] == '') {
                            $rewardPoint = $rewardProductModel->getPoints();
                        }
                        $data = [
                            'product_id' => $rewardProductModel->getProductId(),
                            'points' => $rewardPoint,
                            'seller_id' => 0,
                            'status' => $params['status'],
                            'entity_id' => $rewardProductModel->getEntityId()
                        ];
                    } else {
                        $data = [
                            'product_id' => $productId,
                            'points' => $params['rewardpoint'],
                            'seller_id' => 0,
                            'status' => $params['status']
                        ];
                    }
                    $this->mpRewardHelper->setProductRewardData($data);
                }
                if ($params['rewardpoint'] == '') {
                    $this->messageManager->addSuccess(
                        __("Total of %1 product(s) reward status are updated", count($productIds))
                    );
                } else {
                    $this->messageManager->addSuccess(
                        __("Total of %1 product(s) reward are updated", count($productIds))
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Please Enter a valid rewrad points number to add.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Please select products to set points.')
            );
        }
        return $resultRedirect->setPath('mprewardsystem/product/index');
    }
}
