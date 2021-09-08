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

class Changeproperty extends \Magento\Backend\App\Action
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
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_eventsRepo = $eventsRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $wholedata = $this->getRequest()->getParams();
        $ticket = $this->_ticketsFactory->create()->load($wholedata["id"]);
        $from = "";
        if ($wholedata['field'] == "priority") {
            $from = $ticket->getPriority();

            $ticket->setPriority($wholedata['value']);
            $ticket->save();
            $this->_eventsRepo->checkTicketEvent("priority", $wholedata["id"], $from, $wholedata['value']);
        } elseif ($wholedata['field'] == "status") {
            $from = $ticket->getStatus();
            $ticket->setStatus($wholedata['value']);
            $ticket->save();
            $this->_eventsRepo->checkTicketEvent("status", $wholedata["id"], $from, $wholedata['value']);
        } elseif ($wholedata['field'] == "agent") {
            $from = $ticket->getToAgent();
            $ticket->setToAgent($wholedata['value']);
            $ticket->save();
            $this->_eventsRepo->checkTicketEvent("agent", $wholedata["id"], $from, $wholedata['value']);
        } elseif ($wholedata['field'] == "group") {
            $from = $ticket->getToGroup();
            $ticket->setToGroup($wholedata['value']);
            $ticket->save();
            $this->_eventsRepo->checkTicketEvent("group", $wholedata["id"], $from, $wholedata['value']);
        } elseif ($wholedata['field'] == "type") {
            $from = $ticket->getType();
            $ticket->setType($wholedata['value']);
            $ticket->save();
            $this->_eventsRepo->checkTicketEvent("type", $wholedata["id"], $from, $wholedata['value']);
        }
        $this->_eventsRepo->checkTicketEvent("ticket", $wholedata["id"], "updated");
        $this->getResponse()->setHeader('Content-type', 'text/html');
        $this->getResponse()->setBody(1);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tickets');
    }
}
