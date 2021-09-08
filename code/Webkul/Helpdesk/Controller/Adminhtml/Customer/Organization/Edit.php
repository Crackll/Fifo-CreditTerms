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
namespace Webkul\Helpdesk\Controller\Adminhtml\Customer\Organization;

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
        \Webkul\Helpdesk\Model\CustomerOrganizationFactory $customerOrganizationFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_customerOrganizationFactory = $customerOrganizationFactory;
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::customerorganization')
            ->addBreadcrumb(__('Add Organization'), __('Add Organization'))
            ->addBreadcrumb(__('Manage Organization'), __('Manage Organization'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $orgId = $this->getRequest()->getParam('id');
        /** @var \Webkul\Helpdesk\Model\Group $model */
        $model = $this->_customerOrganizationFactory->create();

        if ($orgId) {
            $model->load($orgId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Organization no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        // Restore previously entered form data from session
        $data = $this->_session->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('customer_organization', $model);

        if (isset($groupId)) {
            $breadcrumb = __('Edit Organization');
        } else {
            $breadcrumb = __('New Organization');
        }

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Organization'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ?
        $model->getName() : __('New Organization'));
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::customerorganization');
    }
}
