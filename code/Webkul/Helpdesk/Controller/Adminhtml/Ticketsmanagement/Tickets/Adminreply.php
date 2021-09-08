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

class Adminreply extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\Helpdesk\Model\TicketsRepository
     */
    protected $_ticketsRepo;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @var \Webkul\Helpdesk\Model\ActivityRepository
     */
    protected $_activityRepo;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @var \Webkul\Helpdesk\Model\TicketsFactory
     */
    protected $ticketFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Helpdesk\Model\TicketsRepository $ticketsRepo,
     * @param \Magento\Backend\Model\Auth\Session $authSession,
     * @param \Magento\User\Model\UserFactory $userFactory,
     * @param \Webkul\Helpdesk\Model\ActivityRepository $activityRepo
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Model\TicketsRepository $ticketsRepo,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\ThreadRepository $threadRepo,
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Model\AttachmentRepository $attachmentRepo,
        \Webkul\Helpdesk\Model\TicketdraftFactory $ticketdraftFactory,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Webkul\Helpdesk\Model\SlaRepository $slaRepo,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketFactory,
        \Webkul\Helpdesk\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_ticketsRepo = $ticketsRepo;
        $this->_authSession = $authSession;
        $this->_userFactory = $userFactory;
        $this->_activityRepo = $activityRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_threadRepo = $threadRepo;
        $this->_eventsRepo = $eventsRepo;
        $this->_attachmentRepo = $attachmentRepo;
        $this->_ticketdraftFactory = $ticketdraftFactory;
        $this->_threadFactory = $threadFactory;
        $this->_slaRepo = $slaRepo;
        $this->ticketFactory = $ticketFactory;
        $this->helper = $helper;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try {
            $wholedata = $this->getRequest()->getPostValue();
            $ticketId = (int)$this->getRequest()->getParam('ticket_id');
            if (!$wholedata) {
                $this->_redirect('*/*/viewreply', ["id"=>$ticketId]);
                $this->messageManager->addError(__('Nothing Found To Send!!'));
                return;
            }
            $agentId = $this->_authSession->getUser()->getId();
            $wholedata["fullname"] = __('Admin');
            $wholedata["checkwhois"] = 1;
            //check if id is agent or admin.
            $roleData = $this->_userFactory->create()->load($agentId)->getRole()->getData();
            $userId = $roleData['user_id'];
            if ($userId) {
                $wholedata["is_admin"] = 0;
            } else {
                $wholedata["is_admin"] = 1;
            }
            $wholedata['customer_id'] = $agentId;
            $wholedata['source'] = "website";
            $ticketData = $this->ticketFactory->create()->load($ticketId);
            $threadId = $this->_threadRepo->createThread($ticketId, $wholedata);
            $adminEmail = $this->helper->getConfigHelpdeskEmail();
            $cc = [];
            $bcc = [];
            $email = [];
            $email[] = $ticketData->getEmail();
            $template_name = "helpdesk/email/helpdesk_from_mail";
            if ($wholedata['thread_type'] == "forward") {
                $email = explode(",", $wholedata['to']);
                $email = array_filter($email);
                $template_name = "helpdesk/email/helpdesk_forward_email";
            }
            if (!empty($wholedata['cc'])) {
                $cc = explode(",", $wholedata['cc']);
                $cc = array_filter($cc);
            }
            if (!empty($wholedata['bcc'])) {
                $bcc = explode(",", $wholedata['bcc']);
                $bcc = array_filter($bcc);
            }

            $receiverInfo = ['name'=>$ticketData->getFullname(),'email'=>$email,'cc'=>$cc,'bcc'=>$bcc];
            $senderInfo = ['name'=>'Admin','email'=>$adminEmail];
            $emailTempVariables['name'] = $receiverInfo['name'];
            $emailTempVariables['ticket_id'] = $ticketId;
            $emailTempVariables['body'] = strip_tags($wholedata['query']);
            $emailTempVariables['customer_name'] = $ticketData->getFullname();
            $emailTempVariables['customer_email'] = $ticketData->getEmail();
            $this->helper->sendMail(
                $template_name,
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            $this->_slaRepo->checkTicketConditionForSla($ticketId, "reply");
            $this->_activityRepo->saveActivity($threadId, "Reply", "add", "ticket");
            $draftCollection = $this->_ticketdraftFactory->create()->getCollection()
                                ->addFieldToFilter("ticket_id", ["eq"=>$ticketId])
                                ->addFieldToFilter("user_id", ["eq"=>$agentId])
                                ->addFieldToFilter("field", ["eq"=>$wholedata["thread_type"]]);

            foreach ($draftCollection as $draft) {
                $draft->delete();
            }

            if ($wholedata["thread_type"] == "reply") {
                $this->_eventsRepo->checkTicketEvent("reply", $ticketId, "agent");
            } else {
                $this->_eventsRepo->checkTicketEvent("note", $ticketId, $wholedata["thread_type"]);
            }
            $this->messageManager->addSuccess(__("Message Sent Successfully"));
            $files = $this->getRequest()->getFiles();
            if (isset($files["fupld"]["tmp_name"][0])) {
                $this->_attachmentRepo->saveAttachment($ticketId, $threadId);
            } else {
                $this->_threadFactory->create()->load($threadId)->setAttachment(0)->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('helpdesk/*/viewreply', ["id"=>$ticketId]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tickets');
    }
}
