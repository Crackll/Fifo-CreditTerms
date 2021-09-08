<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Responses;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Applyresponse extends \Magento\Backend\App\Action
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
        \Webkul\Helpdesk\Model\ResponsesFactory $responseFactory,
        \Webkul\Helpdesk\Model\ResponsesRepository $responseRepo
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->responsesFactory = $responseFactory;
        $this->responsesRepo = $responseRepo;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $ticketId = (int)$this->getRequest()->getParam("id");
        $responseId = (int)$this->getRequest()->getParam("responseid");
        $responseModel = $this->responsesFactory->create()->load($responseId);
        $this->responsesRepo->applyResponseToTicket($ticketId, $responseModel->getActions());
        $this->messageManager->addSuccess(__("Response has been successfully applied."));
        $this->_redirect('helpdesk/ticketsmanagement_tickets/viewreply', ["id"=>$ticketId]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::responses');
    }
}
