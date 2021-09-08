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

namespace Webkul\MpRewardSystem\Controller\Adminhtml\Cart;

use Webkul\MpRewardSystem\Controller\Adminhtml\Cart as CartController;
use Magento\Backend\App\Action;
use Webkul\MpRewardSystem\Model\RewardcartFactory;

class Edit extends CartController
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Webkul\MpRewardSystem\Model\RewardcartFactory
     */
    protected $rewardcartFactory;
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param RewardcartFactory $rewardcartFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        RewardcartFactory $rewardcartFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->rewardcartFactory = $rewardcartFactory;
    }
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpRewardSystem::menu')
            ->addBreadcrumb(__('Reward System Credit Rule'), __('Reward System Credit Rule'));
        return $resultPage;
    }
    /**
     * check the reward cart on id basis
     *
     * @return redirected page
     */
    public function execute()
    {
        $flag = 0;
        $id = $this->getRequest()->getParam('id');
        $model = $this->rewardcartFactory
            ->create();
        if ($id) {
            $model->load($id);
            $flag = 1;
            if (!$model->getEntityId()) {
                $this->messageManager->addError(
                    __('This rule no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('mprewardsystem/cart/index');
            }
        }
        $data = $this->_session
                ->getFormData(true);

        if (isset($data) && $data) {
            $model->setData($data);
            $flag = 1;
        }
        $this->coreRegistry->register('reward_cartrewardData', $model);
        $resultPage = $this->_initAction();
        if ($flag==1 && $id) {
            $resultPage->addBreadcrumb(__('Edit Reward Cart Rule'), __('Edit Reward Cart Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Update Reward Cart Rule'));
        } else {
            $resultPage->addBreadcrumb(__('Add Reward Cart Rule'), __('Add Reward Cart Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Add Reward Cart Rule'));
        }
        return $resultPage;
    }
}
