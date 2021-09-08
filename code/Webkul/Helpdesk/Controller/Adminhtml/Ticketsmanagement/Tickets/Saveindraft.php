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

class Saveindraft extends \Magento\Backend\App\Action
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
        \Webkul\Helpdesk\Model\TicketdraftFactory $ticketdraftFactory,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_ticketdraftFactory = $ticketdraftFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $flag = 0;
        $wholedata = $this->getRequest()->getParams();
        $ticketCollection = $this->_ticketdraftFactory->create()->getCollection()
                                ->addFieldToFilter("ticket_id", ["eq"=>$wholedata["id"]])
                                ->addFieldToFilter("user_id", ["eq"=>$wholedata["user_id"]])
                                ->addFieldToFilter("user_type", ["eq"=>"admin"])
                                ->addFieldToFilter("field", ["eq"=>$wholedata["field"]]);
        if (count($ticketCollection)) {
            foreach ($ticketCollection as $draft) {
                $draft->setContent($wholedata["content"]);
                $draft->save();
                $flag = 1;
            }
        } else {
            $draft = $this->_ticketdraftFactory->create();
            $draft->setTicketId($wholedata["id"]);
            $draft->setUserId($wholedata["user_id"]);
            $draft->setUserType("admin");
            $draft->setContent($wholedata["content"]);
            $draft->setField($wholedata["field"]);
            $draft->save();
            $flag = 1;
        }
        $this->getResponse()->setHeader('Content-type', 'text/html');
        $this->getResponse()->setBody($flag);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tickets');
    }
}
