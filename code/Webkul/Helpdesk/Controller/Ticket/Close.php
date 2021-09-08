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
class Close extends Action
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
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Helper\Tickets $ticketsHelper,
        \Webkul\Helpdesk\Helper\Data $helper,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->_eventsRepo = $eventsRepo;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_helper = $helper;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_ticketsHelper = $ticketsHelper;
    }

    /**
     * seller product save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $ticketId = (int)$this->getRequest()->getParam("id");
        if ($ticketId > 0) {
            try {
                $userId = $this->_ticketsHelper->getTsCustomerId();
                if (!$userId) {
                    $this->_redirect("helpdesk/ticket/login/");
                } else {
                    $closeStatus = $this->_helper->getConfigCloseStatus();
                    $ticketCollection = $this->_ticketsFactory->create()->getCollection()
                                                ->addFieldToFilter('entity_id', ["eq"=>$ticketId])
                                                ->addFieldToFilter('customer_id', ['eq'=>$userId]);
                    if (count($ticketCollection)) {
                        foreach ($ticketCollection as $ticket) {
                            $ticket->setStatus($closeStatus);
                            $ticket->save();
                            $this->_eventsRepo->checkTicketEvent("ticket", $ticket->getEntityId(), "updated");
                        }
                        $this->messageManager->addSuccess(__("Ticket was successfully updated"));
                    } else {
                        $this->messageManager->addError(__("Unauthorised user!!"));
                    }
                }
            } catch (\Exception $e) {
                $this->_helpdeskLogger->info($e->getMessage());
            }
        }
        $this->_redirect('helpdesk/ticket/view/', ["id"=>$ticketId]);
    }
}
