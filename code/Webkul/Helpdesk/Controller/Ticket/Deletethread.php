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
class Deletethread extends Action
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
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Helper\Tickets $ticketsHelper,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->_activityRepo = $activityRepo;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_ticketsHelper = $ticketsHelper;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_threadFactory = $threadFactory;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * seller product save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $threadId = $this->getRequest()->getParam("id");
        $flag = 0;
        if ($threadId > 0) {
            try {
                $userId = $this->_ticketsHelper->getTsCustomerId();
                if (!$userId) {
                    $this->_redirect("helpdesk/ticket/login/");
                } else {
                    $thread = $this->_threadFactory->create()->load($threadId);
                    $ticketColl = $this->_ticketsFactory->create()->getCollection()
                                                        ->addFieldToFilter("entity_id", ["eq"=>$thread->getTicketId()])
                                                        ->addFieldToFilter("customer_id", ["eq"=>$userId]);
                    if ($thread) {
                        $this->_activityRepo->saveActivity($threadId, "Query", "delete", "ticket");
                        $thread->delete();
                        $flag = 1;
                    }
                }
            } catch (\Exception $e) {
                $this->_helpdeskLogger->info($e->getMessage());
            }
        }
        $result = $this->jsonResultFactory->create();
        $result->setData($flag);
        return $result;
    }
}
