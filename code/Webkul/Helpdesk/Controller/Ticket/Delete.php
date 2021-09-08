<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Controller\Ticket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Webkul Marketplace Product Save Controller.
 */
class Delete extends Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @param Context          $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo,
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\ThreadRepository $threadRepo,
        \Webkul\Helpdesk\Helper\Tickets $ticketsHelper,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->_activityRepo = $activityRepo;
        $this->_eventsRepo = $eventsRepo;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_threadRepo = $threadRepo;
        $this->_ticketsHelper = $ticketsHelper;
        $this->_helpdeskLogger = $helpdeskLogger;
    }

    /**
     * seller product save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $ticketId = $this->getRequest()->getParam("id");
        if ($ticketId > 0) {
            try {
                $userId = $this->_ticketsHelper->getTsCustomerId();
                if (!$userId) {
                    $this->_redirect("helpdesk/ticket/login/");
                } else {
                    $ticketCollection = $this->_ticketsFactory->create()->getCollection()
                                                ->addFieldToFilter('entity_id', ["eq"=>$ticketId])
                                                ->addFieldToFilter('customer_id', ['eq'=>$userId]);
                    if (count($ticketCollection)) {
                        foreach ($ticketCollection as $ticket) {
                            $this->_eventsRepo->checkTicketEvent("ticket", $ticket->getEntityId(), "deleted");
                            $this->_activityRepo->saveActivity(
                                $ticket->getEntityId(),
                                $ticket->getSubject(),
                                "delete",
                                "ticket"
                            );
                            $this->_threadRepo->deleteThreads($ticket->getEntityId());
                            $ticket->delete();
                        }
                        $this->messageManager->addSuccess(__("Ticket was successfully deleted"));
                    } else {
                        $this->messageManager->addError(__("Unauthorised user!!"));
                    }
                }
            } catch (\Exception $e) {
                $this->_helpdeskLogger->info($e->getMessage());
            }
        }
        $this->_redirect('helpdesk/ticket/mytickets/');
    }
}
