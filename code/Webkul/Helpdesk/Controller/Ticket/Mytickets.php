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
class Mytickets extends Action
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
        \Webkul\Helpdesk\Helper\Tickets $ticketHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_ticketHelper = $ticketHelper;
        parent::__construct($context);
    }

    /**
     * Helpdesk Support page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_ticketHelper->unSetTsCustomerData();
        if ($this->_ticketHelper->getCustomerSession()->isLoggedIn()) {
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__("Ticket Listing"));
            return $resultPage;
        } else {
            $this->_redirect("helpdesk/ticket/login/");
        }
    }
}
