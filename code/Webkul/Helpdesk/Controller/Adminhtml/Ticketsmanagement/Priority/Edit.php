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
namespace Webkul\Helpdesk\Controller\Adminhtml\Ticketsmanagement\Priority;

use Magento\Backend\App\Action;

class Edit extends Action
{
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketPriorityFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketPriorityFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_ticketPriorityFactory = $ticketPriorityFactory;
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::priority')
            ->addBreadcrumb(__('Add Priority'), __('Add Priority'))
            ->addBreadcrumb(__('Manage Priority'), __('Manage Priority'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $priorityId = (int)$this->getRequest()->getParam('id');
        $prioritymodel = $this->_ticketPriorityFactory->create();
        if ($priorityId) {
            $prioritymodel->load($priorityId);
            if (!$prioritymodel->getId()) {
                $this->messageManager->addError(__('This ticket priority no longer exists.'));
                $this->_redirect('helpdesk/*/');
                return;
            }
        }

        $this->_coreRegistry->register('helpdesk_ticket_priority', $prioritymodel);

        if (isset($priorityId)) {
            $breadcrumb = __('Edit Priority');
        } else {
            $breadcrumb = __('New Priority');
        }
        $this->_initAction()->addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::priority');
    }
}
