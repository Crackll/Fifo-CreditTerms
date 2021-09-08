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
namespace Webkul\Helpdesk\Controller\Adminhtml\Businesshours;

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
        \Webkul\Helpdesk\Model\BusinesshoursFactory $businesshoursFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_businesshoursFactory = $businesshoursFactory;
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::businesshours')
            ->addBreadcrumb(__('Add Businesshours'), __('Add Businesshours'))
            ->addBreadcrumb(__('Manage Businesshours'), __('Manage Businesshours'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $businesshoursId = $this->getRequest()->getParam('entity_id');
        /** @var \Webkul\Helpdesk\Model\Group $model */
        $model = $this->_businesshoursFactory->create();

        if ($businesshoursId) {
            $model->load($businesshoursId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Business hours no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        // Restore previously entered form data from session
        $data = $this->_session->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('helpdesk_businesshours', $model);

        
        $resultPage = $this->_resultPageFactory->create();
        if ($model->getId()) {
            $breadcrumb = __('Edit Businesshours');
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Businesshours'));

        } else {
            $breadcrumb = __('New Businesshours');
            $resultPage->getConfig()->getTitle()->prepend(__('New Businesshours'));
        }
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::businesshours');
    }
}
