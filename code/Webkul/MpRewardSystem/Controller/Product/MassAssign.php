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

namespace Webkul\MpRewardSystem\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Webkul\MpRewardSystem\Model\RewardproductFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Webkul MpRewardSystem Product MassAssign Rewards controller.
 */
class MassAssign extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var Webkul\MpRewardSystem\Model\RewardproductFactory
     */
    protected $rewardProduct;
    /**
     * @var Webkul\MpRewardSystem\Helper\Data
     */
    protected $mpRewardHelper;
    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @var HelperData
     */
    protected $helper;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param RewardproductFactory $rewardProduct
     * @param \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper
     * @param HelperData $helper
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Customer\Model\Url $url
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        RewardproductFactory $rewardProduct,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        HelperData $helper,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Customer\Model\Url $url
    ) {
        $this->customerSession = $customerSession;
        $this->rewardProduct = $rewardProduct;
        $this->mpRewardHelper = $mpRewardHelper;
        $this->helper = $helper;
        $this->formKeyValidator = $formKeyValidator;
        $this->jsonDecoder = $jsonDecoder;
        parent::__construct(
            $context
        );
        $this->url=$url;
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
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Mass delete seller products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $isPartner = $this->helper->isSeller();
            if ($isPartner == 1) {
                try {
                    if (!$this->formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/index',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $productIds=[];
                    $wholedata = $this->getRequest()->getParams();
                    $productData= $this->getRequest()->getParam('checkedData');
                    $data=(array)$this->jsonDecoder->decode($productData);
                    if (trim($productData) != null && !empty($data)) {
                        $productIds =  $this->productArray($data);
                    } else {
                        $productIds = $this->getRequest()->getParam('reward_mass_assign');
                    }
                    $this->assignProductReward($productIds, $wholedata);
                    if ($wholedata['rewardpoint'] == '') {
                        $this->messageManager->addSuccess(
                            __("Total of %1 product(s) reward status are updated", count($productIds))
                        );
                    } else {
                        $this->messageManager->addSuccess(
                            __("Total of %1 product(s) reward are updated", count($productIds))
                        );
                    }
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
     /**
      * provide the product ids from the data
      *
      * @param [] $data
      * @return array
      */
    public function productArray($data)
    {
        $productIds=[];
        foreach ($data as $key => $value) {
            if ($value == "true") {
                $productIds[] = $key;
            }
        }
         return $productIds;
    }
    /**
     * set and assign the product reward
     *
     * @param [] $productIds
     * @param [] $wholedata
     */
    public function assignProductReward($productIds, $wholedata)
    {
        foreach ($productIds as $productId) {
            $rewardProductModel = $this->rewardProduct->create()->load($productId, 'product_id');
            if ($rewardProductModel->getEntityId()) {
                $rewardPoint = $wholedata['rewardpoint'];
                if ($wholedata['rewardpoint'] == '') {
                    $rewardPoint = $rewardProductModel->getPoints();
                }
                $data = [
                    'product_id' => $rewardProductModel->getProductId(),
                    'points' => $rewardPoint,
                    'seller_id' => $this->mpRewardHelper->getPartnerId(),
                    'status' => $wholedata['status'],
                    'entity_id' => $rewardProductModel->getEntityId()
                ];
            } else {
                $data = [
                    'product_id' => $productId,
                    'points' => $wholedata['rewardpoint'],
                    'seller_id' => $this->mpRewardHelper->getPartnerId(),
                    'status' => $wholedata['status']
                ];
            }
            $this->mpRewardHelper->setProductRewardData($data);
        }
    }
}
