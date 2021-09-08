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
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Helper\Tickets $ticketHelper,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_ticketHelper = $ticketHelper;
        $this->_ticketsFactory = $ticketsFactory;
        parent::__construct($context);
    }

    /**
     * Helpdesk Support page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ticketId = (int)$this->getRequest()->getParam("id");
        if ($this->_ticketHelper->getCustomerSession()->isLoggedIn()) {
            $customerId = $this->_ticketHelper->getCustomerSession()->getCustomer()->getId();
            $helpdeskCustomer = $this->_ticketHelper->getHelpdeskCustomerByMageCustomerId($customerId);
            if (count($helpdeskCustomer->getData())) {
                $ticketColl = $this->_ticketsFactory->create()->getCollection()
                                                    ->addFieldToFilter("entity_id", ["eq"=>$ticketId])
                                                    ->addFieldToFilter(
                                                        "customer_id",
                                                        ["eq"=>$helpdeskCustomer->getId()]
                                                    );
                if ($ticketColl->getSize()) {
                    $resultPage = $this->_resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->set(__("View Ticket"));
                    return $resultPage;
                } else {
                    $this->messageManager->addError(__("You are not authorized user!!"));
                    $this->_redirect("helpdesk/ticket/mytickets/");
                }
            } else {
                $this->messageManager->addError(__("You are not authorized user!!"));
                $this->_redirect("helpdesk/ticket/mytickets/");
            }
        } else {
            $guestCustomerData = $this->_ticketHelper->getTsCustomerData();
            if (isset($guestCustomerData['id']) && $ticketId==$guestCustomerData['id']) {
                $resultPage = $this->_resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__("View Ticket"));
                return $resultPage;
            } else {
                $this->messageManager->addError(__("You are not authorized user!!"));
                return $this->_redirect("helpdesk/ticket/login/");
            }
        }
    }
}
