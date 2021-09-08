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

class Splitthread extends \Magento\Backend\App\Action
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
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo,
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Model\TicketsRepository $ticketsRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_threadFactory = $threadFactory;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_authSession = $authSession;
        $this->_activityRepo = $activityRepo;
        $this->_eventsRepo = $eventsRepo;
        $this->_ticketsRepo = $ticketsRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam("id");
        $thread = $this->_threadFactory->create()->load($id);
        $ticketId = $thread->getTicketId();
        $ticket = $this->_ticketsFactory->create()->load($ticketId);
        $data = [];
        $data['fullname'] = $ticket->getFullname();
        $data['email'] = $ticket->getEmail();
        $data['subject'] = $ticket->getEmail();
        $data['query'] = $thread->getReply();
        $data['source'] = "website";
        $data['who_is'] = "agent";
        $ticketId = $this->_ticketsRepo->createTicket($data);
        $thread->setTicketId($ticketId);
        $thread->setWhoIs($data['who_is']);
        $thread->setThreadType("create");
        $thread->save();
        $this->_activityRepo->saveActivity($ticketId, "", "add", "ticket");
        $this->_eventsRepo->checkTicketEvent("ticket", $ticketId, "created");
        $this->getResponse()->setHeader('Content-type', 'text/html');
        $this->getResponse()->setBody($ticketId);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tickets');
    }
}
