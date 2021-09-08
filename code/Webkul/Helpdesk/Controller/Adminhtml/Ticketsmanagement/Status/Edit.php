<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Ticketsmanagement\Status;

use Magento\Backend\App\Action;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\Helpdesk\Model\Tickets
     */
    protected $_ticketStatusModel;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\Helpdesk\Model\Tickets $ticketStatusModel
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketStatusFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_ticketStatusFactory = $ticketStatusFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::status');
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::status')
            ->addBreadcrumb(__('Ticket Status'), __('Ticket Status'))
            ->addBreadcrumb(__('Manage Ticket Status'), __('Manage Ticket Status'));
        return $resultPage;
    }

    /**
     * Add Tickets
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $statusId = (int)$this->getRequest()->getParam('id');
        $statusmodel = $this->_ticketStatusFactory->create();
        if ($statusId) {
            $statusmodel->load($statusId);
            if (!$statusmodel->getId()) {
                $this->messageManager->addError(__('This status no longer exists.'));
                $this->_redirect('helpdesk/*/');
                return;
            }
        }

        $this->_coreRegistry->register('helpdesk_ticket_status', $statusmodel);

        if (isset($statusId)) {
            $breadcrumb = __('Edit Status');
        } else {
            $breadcrumb = __('New Status');
        }
        $this->_initAction()->addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }
}
