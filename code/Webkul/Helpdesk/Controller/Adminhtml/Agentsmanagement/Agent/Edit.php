<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Controller\Adminhtml\Agentsmanagement\Agent;

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
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
        \Magento\User\Model\UserFactory $userFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_agentFactory = $agentFactory;
        $this->_userFactory = $userFactory;
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::agent')
            ->addBreadcrumb(__('Add Agent'), __('Add Agent'))
            ->addBreadcrumb(__('Manage Agent'), __('Manage Agent'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $userId = $this->getRequest()->getParam('user_id');
        /** @var \Webkul\Helpdesk\Model\Agent $model */
        $model = $this->_userFactory->create();

        if ($userId) {
            $model->load($userId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This user no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        } else {
            $model->setInterfaceLocale(Resolver::DEFAULT_LOCALE);
            // $model->
        }

        // Restore previously entered form data from session
        $data = $this->_session->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $agentModel = $this->_agentFactory->create()
                           ->getCollection()
                           ->addFieldToFilter("user_id", ["eq"=>$userId])
                           ->getFirstItem();
        $model->setTicketScope($agentModel->getTicketScope());
        $model->setTimezone($agentModel->getTimezone());
        $model->setLevel($agentModel->getLevel());
        $model->setSignature($agentModel->getSignature());
        $model->setGroupId($agentModel->getGroupId());
        $model->setEntityId($agentModel->getId());
        $this->_coreRegistry->register('permissions_agent', $model);

        if (isset($userId)) {
            $breadcrumb = __('Edit Agent');
        } else {
            $breadcrumb = __('New Agent');
        }
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Users'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ?
        $model->getName() : __('New Agent'));
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::agent');
    }
}
