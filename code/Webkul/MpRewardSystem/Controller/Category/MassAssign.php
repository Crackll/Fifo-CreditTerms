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

namespace Webkul\MpRewardSystem\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Webkul\MpRewardSystem\Model\RewardcategoryFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Webkul MpRewardSystem Category MassAssign Rewards controller.
 */
class MassAssign extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var Webkul\MpRewardSystem\Model\RewardcategoryFactory
     */
    protected $rewardCategory;
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
     * @param RewardcategoryFactory $rewardCategory
     * @param \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper
     * @param HelperData $helper
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Customer\Model\Url $url
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        RewardcategoryFactory $rewardCategory,
        \Webkul\MpRewardSystem\Helper\Data $mpRewardHelper,
        HelperData $helper,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Customer\Model\Url $url
    ) {
        $this->customerSession = $customerSession;
        $this->rewardCategory = $rewardCategory;
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
     * Mass Assign seller Category action.
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
                    $categoryIds=[];
                    $wholedata = $this->getRequest()->getParams();
                    $categoryData= $this->getRequest()->getParam('checkedData');
                    $data=(array)$this->jsonDecoder->decode($categoryData);
                    if (trim($categoryData) != null && !empty($data)) {
                        $categoryIds = $this->categoryArray($data);
                    } else {
                        $categoryIds=$this->getRequest()->getParam('reward_mass_assign');
                    }
                    $this->assignCategoryReward($categoryIds, $wholedata);
                    if ($wholedata['rewardpoint'] == '') {
                        $this->messageManager->addSuccess(
                            __("Total of %1 category(s) reward status are updated", count($categoryIds))
                        );
                    } else {
                        $this->messageManager->addSuccess(
                            __("Total of %1 category(s) reward are updated", count($categoryIds))
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
     * provide the category ids on the data
     *
     * @param [] $data
     * @return array
     */
    public function categoryArray($data)
    {
        $categoryIds=[];
        foreach ($data as $key => $value) {
            if ($value == "true") {
                $categoryIds[] = $key;
            }
        }
         return $categoryIds;
    }
    /**
     * assign the category rewards
     *
     * @param [] $categoryIds
     * @param [] $wholedata
     * @return void
     */
    public function assignCategoryReward($categoryIds, $wholedata)
    {
        foreach ($categoryIds as $categoryId) {
            $rewardCategoryModel = $this->rewardCategory->create()->load($categoryId, 'category_id');
            if ($rewardCategoryModel->getEntityId()) {
                $rewardPoint = $wholedata['rewardpoint'];
                if ($wholedata['rewardpoint'] == '') {
                    $rewardPoint = $rewardCategoryModel->getPoints();
                }
                $data = [
                    'category_id' => $rewardCategoryModel->getCategoryId(),
                    'points' => $rewardPoint,
                    'seller_id' => $this->mpRewardHelper->getPartnerId(),
                    'status' => $wholedata['status'],
                    'entity_id' => $rewardCategoryModel->getEntityId()
                ];
            } else {
                $data = [
                    'category_id' => $categoryId,
                    'points' => $wholedata['rewardpoint'],
                    'seller_id' => $this->mpRewardHelper->getPartnerId(),
                    'status' => $wholedata['status']
                ];
            }
            $this->mpRewardHelper->setCategoryRewardData($data);
        }
    }
}
