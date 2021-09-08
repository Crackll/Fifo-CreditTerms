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

class Saveattributevalues extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\Helpdesk\Model\TicketsAttributeValueRepository
     */
    protected $_ticketsAttrValRepo;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory,
     * @param \Webkul\Helpdesk\Model\TicketsAttributeValueRepository $ticketsAttrValRepo,
     * @param \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Model\TicketsAttributeValueRepository $ticketsAttrValRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_ticketsAttrValRepo = $ticketsAttrValRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try {
            $wholedata = $this->getRequest()->getParams();
            $ticketId = $wholedata['id'];
            if ($this->getRequest()->getPost()) {
                $this->_ticketsAttrValRepo->editTicketAttributeValues($wholedata);
                $this->messageManager->addSuccess(__("Success ! you have been successfully modified Tickets."));
            } else {
                $this->messageManager->addError(__("Sorry Nothing Found To Save!!"));
            }
            $this->_redirect("*/*/viewreply", ['id'=>$ticketId]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            $this->messageManager->addError("There are some error occurring during this action");
        }
        $this->_redirect("*/*/viewreply", ['id'=>$ticketId]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tickets');
    }
}
