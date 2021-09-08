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

class Merge extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\Helpdesk\Model\TicketsFactory
     */
    protected $_ticketsFactory;

    /**
     * @var \Webkul\Helpdesk\Model\ThreadFactory
     */
    protected $_threadFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Helpdesk\Model\TicketsRepository $ticketsRepo,
     * @param \Magento\Backend\Model\Auth\Session $authSession,
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\ThreadRepository $threadRepo,
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Helper\Data $helperData
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_threadFactory = $threadFactory;
        $this->_authSession = $authSession;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_threadRepo = $threadRepo;
        $this->_eventsRepo = $eventsRepo;
        $this->_helperData = $helperData;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            if (!$data) {
                $this->_redirect('helpdesk/ticketsmanagement_tickets/index');
                $this->messageManager->addError(__('Unable to find ticket to merge'));
                return;
            }
            $primaryTicketId = $this->getRequest()->getParam("id");
            $primaryTicketNote = "<strong>Ticket(s) Merged with Ticket - </strong><br/>";
            $agentId = $this->_authSession->getUser()->getId();
            $collection = $this->_ticketsFactory->create()->getCollection();
            $flag = 0;
            foreach ($data['ticket_id'] as $ticketId) {
                $collection->addFieldToFilter('main_table.merge_tickets', ["finset"=>$ticketId]);
                if ($collection->getSize()) {
                    $this->messageManager->addError(__("Ticket already merged!!"));
                    return $this->_redirect("helpdesk/ticketsmanagement_tickets/index");
                }
                $is_primary = 0;
                if ($ticketId == $primaryTicketId) {
                    $is_primary = 1;
                }
                $tickets = implode(',', $data['ticket_id']);
                $ticketData = $this->_ticketsFactory->create()->load($ticketId);
                $ticketData->setIsMerged('1');
                $ticketData->setMergePrimaryId($is_primary);
                $ticketData->setMergeTickets($tickets);
                $ticketData->save();

                if ($primaryTicketId == $ticketId) {
                    continue;
                }
                $threadCollection = $this->_threadFactory->create()->getCollection()
                                        ->addFieldToFilter("thread_type", ["neq"=>"create"])
                                        ->addFieldToFilter("ticket_id", $ticketId);
                foreach ($threadCollection as $thread) {
                    $thread->setTicketId($primaryTicketId);
                    $thread->save();
                }
                $wholedata = [];
                $closeStatus = $this->_helperData->getConfigCloseStatus();
                $ticket = $this->_ticketsFactory->create()->load($ticketId);
                $from = $ticket->getStatus();
                $ticket->setStatus($closeStatus);
                $ticket->save();
                $this->_eventsRepo->checkTicketEvent("status", $ticketId, $from, $closeStatus);
                $wholedata["checkwhois"] = 1;
                $wholedata["is_admin"] = 1;
                $wholedata['source'] = "website";
                $wholedata['thread_type'] = "note";
                $wholedata['query'] = "This Ticket is Closed and Merged with Ticket Id #".$primaryTicketId;
                $threadId = $this->_threadRepo->createThread($ticketId, $wholedata);
                $this->_eventsRepo->checkTicketEvent("note", $ticketId, "note");
                $primaryTicketNote = $primaryTicketNote."#".$ticketId."<br/>";
                $primaryTicketNote = $primaryTicketNote."Subject - ".$ticket->getSubject()."<br/>";
                $primaryTicketNote = $primaryTicketNote."Message - ".$ticket->getQuery()."<br/><br/>";
            }
            $wholedata = [];
            $wholedata["checkwhois"] = 1;
            $wholedata["is_admin"] = 1;
            $wholedata['source'] = "website";
            $wholedata['thread_type'] = "note";
            $wholedata['query'] = $primaryTicketNote;
            $threadId =  $this->_threadRepo->createThread($primaryTicketId, $wholedata);
            $this->_eventsRepo->checkTicketEvent("note", $primaryTicketId, "note");
            $this->messageManager->addSuccess(__("Success ! you have been successfully merged Tickets."));
            $this->_redirect("helpdesk/ticketsmanagement_tickets/index");
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('helpdesk/ticketsmanagement_tickets/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tickets');
    }
}
