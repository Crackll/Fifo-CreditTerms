<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Ticketsmanagement\Tickets;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\ThreadRepository $threadRepo
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_authSession = $authSession;
        $this->_activityRepo = $activityRepo;
        $this->_eventsRepo = $eventsRepo;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_threadRepo = $threadRepo;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $ticketId = $this->getRequest()->getParam("id");
        if ($ticketId > 0) {
            try {
                $this->_eventsRepo->checkTicketEvent("ticket", $ticketId, "deleted");
                $model = $this->_ticketsFactory->create()->load($ticketId);
                $this->_activityRepo->saveActivity($model->getId(), $model->getSubject(), "delete", "ticket");
                $model->delete();
                $this->_threadRepo->deleteThreads($ticketId);
                $this->messageManager->addSuccess(__("Ticket was successfully deleted"));
                $this->_redirect("*/*/");
            } catch (\Exception $e) {
                $this->_helpdeskLogger->info($e->getMessage());
                $this->_redirect("*/*/edit", ["id" => $ticketId]);
            }
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tickets');
    }
}
