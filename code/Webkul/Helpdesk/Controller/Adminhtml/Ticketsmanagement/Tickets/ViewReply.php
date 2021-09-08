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

namespace Webkul\Helpdesk\Controller\Adminhtml\Ticketsmanagement\Tickets;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class ViewReply extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession,
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->ticketsFactory = $ticketsFactory;
        $this->_authSession = $authSession;
        $this->_agentFactory = $agentFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam("id");
        $ticket = $this->ticketsFactory->create()->load($id);
        $flag = 1;
        if ($ticket->getId()) {
            $agentId = $this->_authSession->getUser()->getId();
            $agentModel = $this->_agentFactory->create()->getCollection()
                                ->addFieldToFilter("user_id", $agentId)
                                ->getFirstItem();

            if ($agentModel->getTicketScope() == 2) {
                if ($ticket->getToGroup()!=$agentModel->getGroupId() || $ticket->getToAgent()!=$agentId) {
                    $flag = 0;
                }
            } elseif ($agentModel->getTicketScope() == 3) {
                $flag = 0;
            }
            if ($flag) {
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('Webkul_Helpdesk::tickets');
                return $resultPage;
            } else {
                $this->messageManager->addError(__("You are not authorized to access this ticket."));
                $this->_redirect("*/*/");
            }
        } else {
            $this->messageManager->addError(__('Ticket does not exist.'));
            $this->_redirect("*/*/");
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tickets');
    }
}
