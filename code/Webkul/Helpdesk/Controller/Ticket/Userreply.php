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
class Userreply extends Action
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
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\TicketdraftFactory $ticketdraftFactory,
        \Webkul\Helpdesk\Model\AttachmentRepository $attachmentRepo,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Webkul\Helpdesk\Helper\Data $helper,
        \Magento\User\Model\UserFactory $userFactory
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
        $this->_ticketdraftFactory = $ticketdraftFactory;
        $this->_attachmentRepo = $attachmentRepo;
        $this->_threadFactory = $threadFactory;
        $this->helper = $helper;
        $this->userFactory = $userFactory;
    }

    /**
     * seller product save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $userId = $this->_ticketsHelper->getTsCustomerId();
            $wholedata = $this->getRequest()->getPostValue();
            $ticketId = (int)$this->getRequest()->getParam('wk_ts_id');
            if (!$userId) {
                return $this->_redirect("helpdesk/ticket/login/");
            } else {
                if ($this->getRequest()->isPost()) {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        $this->messageManager->addError(__("Form key is not valid!!"));
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $tickets_data = $this->_ticketsFactory->create()->load($ticketId);
                    $tickets_data = array_filter($tickets_data->getData());
                    if (!empty($tickets_data)) {
                        $ticketId = $wholedata["wk_ts_id"];
                        $wholedata['thread_type'] = "reply";
                        $wholedata['customer_id'] = $userId;
                        $wholedata['source'] = "website";
                        $threadId = $this->_threadRepo->createThread($ticketId, $wholedata);
                        $adminEmail = $this->helper->getConfigHelpdeskEmail();
                        $cc = [];
                        $bcc = [];
                        if (isset($wholedata['cc'])) {
                            $cc = explode(",", $wholedata['cc']);
                            $cc = array_filter($cc);
                        }
                        if (isset($tickets_data['to_agent'])) {
                            $agent = $this->userFactory->create()->load(
                                $tickets_data['to_agent']
                            );
                            $fullname = $agent->getFirstname()." ".$agent->getLastname();
                            $email = $agent->getEmail();
                        } else {
                            $fullname ='Admin';
                            $email = $adminEmail;
                        }
                        $receiverInfo =
                            [
                                'name'=>$fullname,
                                'email'=>$email,
                                'cc'=>$cc
                            ];
                        $senderInfo = ['name'=>$tickets_data['fullname'],'email'=>$tickets_data['email']];

                        $emailTempVariables['name'] = $receiverInfo['name'];
                        $emailTempVariables['ticket_id'] = $ticketId;
                        $emailTempVariables['body'] = strip_tags($wholedata['query']);
                        $template_name = "helpdesk/email/helpdesk_from_customer";
                        $this->helper->sendMail(
                            $template_name,
                            $emailTempVariables,
                            $senderInfo,
                            $receiverInfo
                        );
                        $this->_activityRepo->saveActivity(
                            $ticketId,
                            "Query",
                            "add",
                            "ticket"
                        );
                        $error = 0;
                        if ($threadId) {
                            $this->_ticketsFactory->create()->load($ticketId)
                            ->setAnswered(0)
                            ->save();
                        }
                        $files = $this->getRequest()->getFiles();
                        if (isset($files["fupld"]["tmp_name"][0])) {
                            $this->_attachmentRepo->saveAttachment($ticketId, $threadId);
                        } else {
                            $this->_threadFactory->create()->load($threadId)->setAttachment(0)->save();
                        }

                        $customerId = $this->_ticketsHelper->getTsCustomerId();
                        $draftCollection = $this->_ticketdraftFactory->create()->getCollection()
                                            ->addFieldToFilter("ticket_id", ["eq"=>$wholedata["wk_ts_id"]])
                                            ->addFieldToFilter("user_id", ["eq"=>$customerId])
                                            ->addFieldToFilter("field", ["eq"=>"reply"]);

                        foreach ($draftCollection as $draft) {
                            $draft->delete();
                        }

                        $this->_eventsRepo->checkTicketEvent("reply", $ticketId, "customer");
                        $this->messageManager->addSuccess(__("Message Sent Successfully"));
                    } else {
                        $this->messageManager->addError(__('Ticket does not exist!!'));
                    }
                } else {
                    $this->messageManager->addError(__('Nothing Found To Send!!'));
                }
            }
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect("*/*/view", ["id"=>$ticketId]);
    }
}
