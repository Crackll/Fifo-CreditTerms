<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Agentsmanagement\Group;

use Magento\Framework\Locale\Resolver;
use Magento\Backend\App\Action;

class Edit extends Action
{
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\Helpdesk\Model\AgentFactory $agentFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_groupFactory = $groupFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Helpdesk::level')
            ->addBreadcrumb(__('Add Group'), __('Add Group'))
            ->addBreadcrumb(__('Manage Group'), __('Manage Group'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $groupId = $this->getRequest()->getParam('entity_id');
        /** @var \Webkul\Helpdesk\Model\Group $model */
        $model = $this->_groupFactory->create();

        if ($groupId) {
            $model->load($groupId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This group no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        // Restore previously entered form data from session
        $data = $this->_session->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('agent_group', $model);

        if (isset($groupId)) {
            $breadcrumb = __('Edit Group');
        } else {
            $breadcrumb = __('New Group');
        }

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Group'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ?
        $model->getName() : __('New Group'));
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::group');
    }
}
