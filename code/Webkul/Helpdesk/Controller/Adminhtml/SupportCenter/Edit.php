<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\SupportCenter;

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
        \Webkul\Helpdesk\Model\SupportCenterFactory $supportCenterFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_supportCenterFactory = $supportCenterFactory;
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::supportcenter')
            ->addBreadcrumb(__('Add Item'), __('Add Item'))
            ->addBreadcrumb(__('Manage Item'), __('Manage Item'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $scId = $this->getRequest()->getParam('id');
        /** @var \Webkul\Helpdesk\Model\Group $model */
        $model = $this->_supportCenterFactory->create();

        if ($scId) {
            $model->load($scId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Item no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // Restore previously entered form data from session
        $data = $this->_session->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('support_center', $model);

        if (isset($groupId)) {
            $breadcrumb = __('Edit Item');
        } else {
            $breadcrumb = __('New Item');
        }

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Item'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Item'));
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::supportcenter');
    }
}
