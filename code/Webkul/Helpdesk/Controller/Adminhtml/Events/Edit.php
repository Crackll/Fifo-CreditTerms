<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Events;

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
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\Helpdesk\Model\Tickets $ticketsModel
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Webkul\Helpdesk\Model\EventsFactory $eventsFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_eventsFactory = $eventsFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::events');
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
        $resultPage->setActiveMenu('Webkul_Helpdesk::events')
            ->addBreadcrumb(__('Events'), __('Events'))
            ->addBreadcrumb(__('Manage Events'), __('Manage Events'));
        return $resultPage;
    }

    /**
     * Edit Tickets
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $eventId = (int)$this->getRequest()->getParam('id');
        $eventsmodel = $this->_eventsFactory->create();
        if ($eventId) {
            $eventsmodel->load($eventId);
            if (!$eventsmodel->getId()) {
                $this->messageManager->addError(__('This Event no longer exists.'));
                $this->_redirect('helpdesk/*/');
                return;
            }
        }

        $this->_coreRegistry->register('helpdesk_events', $eventsmodel);

        $resultPage = $this->_resultPageFactory->create();
        if ($eventId) {
            $breadcrumb = __('Edit Event');
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Event'));

        } else {
            $breadcrumb = __('New Event');
            $resultPage->getConfig()->getTitle()->prepend(__('New Event'));
        }
        return $resultPage;
    }
}
