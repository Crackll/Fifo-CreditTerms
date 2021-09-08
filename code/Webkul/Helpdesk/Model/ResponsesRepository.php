<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model;

use \Magento\Framework\Exception\CouldNotSaveException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ResponsesRepository implements \Webkul\Helpdesk\Api\ResponsesRepositoryInterface
{
    /**
     * @var \Webkul\Helpdesk\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $_sessionManager;

    /**
     * @var \Webkul\Helpdesk\Model\CustomerFactory
     */
    protected $_helpdeskCustomerFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Webkul\Helpdesk\Model\TicketsFactory
     */
    protected $_ticketsFactory;

    /**
     * @var \Webkul\Helpdesk\Model\EmailTemplateRepository
     */
    protected $_emailTemplateRepo;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectmanager;

    /**
     * @var \Webkul\Helpdesk\Model\TagFactory $tagFactory
     */
    protected $_tagFactory;

    /**
     * @var \Webkul\Helpdesk\Model\ThreadRepository
     */
    protected $_threadRepo;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @var \Webkul\Helpdesk\Model\MailfetchFactory
     */
    protected $_mailfetchFactory;

    /**
     * @var \Webkul\Helpdesk\Model\Mail\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $_emailTemplateFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Webkul\Helpdesk\Model\AgentFactory
     */
    protected $_agentFactory;

    /**
     * @var \Webkul\Helpdesk\Model\ThreadFactory
     */
    protected $_threadFactory;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * TicketsRepository constructor.
     * @param \Webkul\Helpdesk\Helper\Data $helper
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Webkul\Helpdesk\Model\CustomerFactory $helpdeskCustomerFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper,
     * @param \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
     * @param \Webkul\Helpdesk\Model\EmailTemplateRepository $emailTemplateRepo,
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager,
     * @param \Webkul\Helpdesk\Model\TagFactory $tagFactory,
     * @param \Webkul\Helpdesk\Model\ThreadRepository $threadRepo,
     * @param \Magento\User\Model\UserFactory $userFactory,
     * @param \Webkul\Helpdesk\Model\MailfetchFactory $mailfetchFactory,
     * @param \Webkul\Helpdesk\Model\Mail\TransportBuilder $transportBuilder,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
     * @param \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
     * @param \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
     * @param \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Webkul\Helpdesk\Helper\Data $helper,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Webkul\Helpdesk\Model\CustomerFactory $helpdeskCustomerFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\EmailTemplateRepository $emailTemplateRepo,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Webkul\Helpdesk\Model\TagFactory $tagFactory,
        \Webkul\Helpdesk\Model\ThreadRepository $threadRepo,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Helpdesk\Model\MailfetchFactory $mailfetchFactory,
        \Webkul\Helpdesk\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Webkul\Helpdesk\Model\EmailTemplateFactory $emailFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Email\Model\BackendTemplateFactory $emailbackendTemp
    ) {
        $this->_helper = $helper;
        $this->_sessionManager = $sessionManager;
        $this->_helpdeskCustomerFactory = $helpdeskCustomerFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_emailTemplateRepo = $emailTemplateRepo;
        $this->_objectmanager = $objectmanager;
        $this->_tagFactory = $tagFactory;
        $this->_threadRepo = $threadRepo;
        $this->_userFactory = $userFactory;
        $this->_mailfetchFactory = $mailfetchFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_emailFactory = $emailFactory;
        $this->_emailTemplateFactory = $emailTemplateFactory;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_agentFactory = $agentFactory;
        $this->_threadFactory = $threadFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_fileDriver = $fileDriver;
        $this->emailbackendTemp = $emailbackendTemp;
    }

    /**
     * applyResponseToTicket This function is resposible for applying the action to the ticket
     * @param Int $ticketId Ticket Id
     * @param JSON $response Response action data
     */
    public function applyResponseToTicket($ticketId, $response)
    {
        try {

            $supportName = $this->_helper->getConfigHelpdeskName();
            $supportEmail = $this->_helper->getConfigHelpdeskEmail();
            $responseAction = json_decode($response, true);

            $ticket = $this->_ticketsFactory->create()->load($ticketId);

            $emailTempVariables = $this->_emailTemplateRepo->getEmailCustomVariables($ticketId);

            $eventModel = $this->_objectmanager->create(\Webkul\Helpdesk\Model\EventsRepository::class);

            if (isset($responseAction['action-type']) && is_array($responseAction['action-type'])) {
                foreach ($responseAction['action-type'] as $actionType) {
                    $this->checkCondition(
                        $actionType,
                        $ticket,
                        $responseAction,
                        $eventModel,
                        $ticketId,
                        $emailTempVariables,
                        $supportName
                    );
                }
            }
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__('Unable to send mail :'.$e->getMessage()), $e);
        }
    }

    //Send mail by template id
    public function sendMailByTemplateId(
        $templateId,
        $emailTempVariables,
        $senderData,
        $customerEmail,
        $customerName,
        $ticketId,
        $ticket
    ) {
        try {
            $emailTempVariables['reply'] = htmlspecialchars_decode($emailTempVariables['reply']);
            $template = $this->_emailFactory->create()->load($templateId);
            $helpdeskTemplateId = $template->getData();
            $templateModel = $this->_emailTemplateFactory->create()->load($helpdeskTemplateId['template_id']);
            $processedTemplate = htmlspecialchars_decode(stripslashes(
                $templateModel->getProcessedTemplate($emailTempVariables)
            ));
            $mail = $this->_transportBuilder;
            $mail->setBodyHTML($processedTemplate);
            $mail->setFrom($senderData);
            $to = explode(',', $emailTempVariables['to']);
            $ticket = $this->_ticketsFactory->create()->load($ticketId);
            if(empty($customerEmail)){
                $customerEmail = $ticket->getEmail();
                $customerName = $ticket->getFullname();
            }
            $to = array_filter($to);
            array_push($to, $customerEmail);
            $mail->addTo("alpna.gargi617@webkul.com", $customerName);

            $session = $this->_sessionManager->getTicketReplyInfo();
            if (!empty($session) && ($session['type'] == "reply" || $session['type'] == "forward")) {
                $lastThread = $this->_threadFactory->create()->getTicketLastThread($ticketId);
                if ($lastThread->getSource() == "website") {
                    $emailAttachmentPath = $this->_helper->getMediaPath()."helpdesk/
                    websiteattachment/".$lastThread->getId(). '/';
                } else {
                    $mailDetails = $this->_mailfetchFactory->create()->getCollection()
                                        ->addFieldToFilter("thread_id", $lastThread->getId())
                                        ->getFirstItem();
                    $emailAttachmentPath = $this->_helper->getMediaPath()."helpdesk/
                    mailattachment/".$mailDetails->getUId(). '/';
                }
                if ($this->_fileDriver->isExists($emailAttachmentPath)) {
                    foreach (new \DirectoryIterator($emailAttachmentPath) as $fileInfo) {
                        if ($fileInfo->isDot() || $fileInfo->isDir()) {
                            continue;
                        }
                        if ($fileInfo->isFile()) {
                            $content = $this->_fileDriver->fileGetContents($fileInfo->getPathname());
                            $attachment = new \Zend_Mime_Part($content);
                            $attachment->type = $this->_transportBuilder->getMimeContentType($fileInfo);
                            $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
                            $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
                            $attachment->filename = $fileInfo;
                            $mail->addAttachment($content, $attachment);
                        }
                    }
                }
            }

            if ($ticket->getSource() == "email") {
                $thread = $this->_threadFactory->create()->getCollection()
                        ->addFieldToFilter("ticket_id", ["eq"=>$ticketId])
                        ->getFirstItem();
                $mailFetch = $this->_mailfetchFactory->create()->getCollection()
                                    ->addFieldToFilter("thread_id", ["eq"=>$thread->getId()])
                                    ->getFirstItem();
                $reference = $mailFetch->getMessageId();
                $mail->addHeader('references', $reference);
            }
            if ($emailTempVariables['cc'] != "") {
                $mail->addCc(explode(',', $emailTempVariables['cc']));
            }

            if ($emailTempVariables['bcc'] != "") {
                $mail->addBcc(explode(',', $emailTempVariables['bcc']));
            }
            $mail->setTemplateIdentifier($templateModel->getId())
                 ->setTemplateOptions(
                     [
                     'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                     'store' => $this->_storeManager->getStore()->getId(),
                     ]
                 );
            $mail->setTemplateVars($emailTempVariables);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    //Send mail by template content
    public function sendMailByTemplateContent(
        $content,
        $emailTempVariables,
        $senderData,
        $customerEmail,
        $customerName,
        $ticketId,
        $ticket,
        $processedSubject,
        $actionType
    ) {
        try {
            $template = $this->emailbackendTemp->create()->getCollection();
            $template->addFieldToFilter('template_code', $actionType);
            foreach ($template as $temp) {
                $temp_id = $temp['template_id'];
            }
            $processedTemplate = $this->_emailTemplateRepo->getProcessedTemplate($content, $emailTempVariables);
            $processedSubject = $this->_emailTemplateRepo->getProcessedTemplate($processedSubject, $emailTempVariables);
            $mail = $this->_transportBuilder;
            $mail->setFrom($senderData);
            $to = explode(',', $emailTempVariables['to']);
            array_push($to, $customerEmail);
            $mail->addTo($customerEmail, $customerName);

            $session = $this->_sessionManager->getTicketReplyInfo();
            if ($session['type'] == "reply" || $session['type'] == "forward") {
                $lastThread = $this->_threadFactory->create()->getTicketLastThread($ticketId);
                if ($lastThread->getSource() == "website") {
                    $emailAttachmentPath = $this->_helper->getMediaPath()."helpdesk/
                    websiteattachment/".$lastThread->getId(). '/';
                } else {
                    $mailDetails = $this->_mailfetchFactory->create()->getCollection()
                                        ->addFieldToFilter("thread_id", $lastThread->getId())
                                        ->getFirstItem();
                    $emailAttachmentPath = $this->_helper->getMediaPath()."helpdesk/
                    mailattachment/".$mailDetails->getUId(). '/';
                }
                if ($this->_fileDriver->isExists($emailAttachmentPath)) {
                    foreach (new \DirectoryIterator($emailAttachmentPath) as $fileInfo) {
                        if ($fileInfo->isDot() || $fileInfo->isDir()) {
                            continue;
                        }
                        if ($fileInfo->isFile()) {
                            $content = $this->_fileDriver->fileGetContents($fileInfo->getPathname());
                            $attachment = new \Zend_Mime_Part($content);
                            $attachment->type = $this->_transportBuilder->getMimeContentType($fileInfo);
                            $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
                            $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
                            $attachment->filename = $fileInfo;
                            $mail->addAttachment($content, $attachment);
                        }
                    }
                }
            }

            if ($ticket->getSource() == "email") {
                $thread = $this->_threadFactory->create()->getCollection()
                        ->addFieldToFilter("ticket_id", ["eq"=>$ticketId])
                        ->getFirstItem();
                $mailFetch = $this->_mailfetchFactory->create()->getCollection()
                                    ->addFieldToFilter("thread_id", ["eq"=>$thread->getId()])
                                    ->getFirstItem();
                $reference = $mailFetch->getMessageId();
                $mail->addHeader('references', $reference);
            }
            if ($emailTempVariables['cc'] != "") {
                $mail->addCc(explode(',', $emailTempVariables['cc']));
            }

            if ($emailTempVariables['bcc'] != "") {
                $mail->addBcc(explode(',', $emailTempVariables['bcc']));
            }

            $mail->setTemplateIdentifier($temp_id);
            $mail->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
                ]);
            $mail->setTemplateVars($emailTempVariables);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    public function checkCondition($actionType, $ticket, $responseAction, $eventModel, $ticketId, $emailTempVariables, $supportName)
    {
        switch ($actionType) {
            case "priority":
                $from = $ticket->getPriority();
                $ticket->setPriority($responseAction['priority']['0']);
                $ticket->save();
                $eventModel->checkTicketEvent(
                    "priority",
                    $ticketId,
                    $from,
                    $responseAction['priority']['0']
                );
                break;
            case "type":
                $from = $ticket->getType();
                $ticket->setType($responseAction['type']['0']);
                $ticket->save();
                $eventModel->checkTicketEvent("type", $ticketId, $from, $responseAction['type']['0']);
                break;
            case "status":
                $from = $ticket->getStatus();
                $ticket->setStatus($responseAction['status']['0']);
                $ticket->save();
                $eventModel->checkTicketEvent("status", $ticketId, $from, $responseAction['status']['0']);
                break;
            case "cc":
                foreach ($responseAction['cc'] as $cc) {
                    $ccs = explode(',', $ticket->getCc());
                    if (!in_array($cc, $ccs)) {
                        array_push($ccs, $cc);
                    }
                    $ccs = array_unique($ccs);
                    $ticket->setCc(implode(',', $ccs));
                    $ticket->save();
                }
                break;
            case "bcc":
                foreach ($responseAction['bcc'] as $bcc) {
                    $bccs = explode(',', $ticket->getBcc());
                    if (!in_array($bcc, $bccs)) {
                        array_push($bccs, $bcc);
                    }
                    $bccs = array_unique($bccs);
                    $ticket->setBcc(implode(',', $bccs));
                    $ticket->save();
                }
                break;
            case "tag":
                foreach ($responseAction['tag'] as $tagId) {
                    $tag = $this->_tagFactory->create()->load($tagId);
                    $ticketIds = explode(',', $tag->getTicketIds());
                    if (!in_array($ticketId, $ticketIds)) {
                        array_push($ticketIds, $ticketId);
                    }
                    $ticketIds = array_unique($ticketIds);
                    $tag->setTicketIds(implode(',', $ticketIds));
                    $tag->save();
                }
                break;
            case "assign_group":
                $from = $ticket->getToGroup();
                $ticket->setToGroup($responseAction['assign_group']['0']);
                $ticket->save();
                $eventModel->checkTicketEvent(
                    "group",
                    $ticketId,
                    $from,
                    $responseAction['assign_group']['0']
                );
                break;
            case "assign_agent":
                $from = $ticket->getToAgent();
                $ticket->setToAgent($responseAction['assign_agent']['0']);
                $ticket->save();
                $eventModel->checkTicketEvent(
                    "agent",
                    $ticketId,
                    $from,
                    $responseAction['assign_agent']['0']
                );
                break;
            case "note":
                $wholedata = [];
                $wholedata['fullname'] = __('System');
                $wholedata['thread_type'] = 'note';
                $wholedata['checkwhois'] = 1;
                $wholedata["is_admin"] = 1;
                $wholedata['query'] = $responseAction['note']['0'];
                $threadId = $this->_threadRepo->createThread(
                    $ticket->getId(),
                    $wholedata
                );
                $eventModel->checkTicketEvent("note", $ticketId, "note");
                break;
            case "mark_spam":
                $ticket->setStatus($this->_helper->getConfigSpamStatus());
                $ticket->save();
                break;
            case "delete_ticket":
                $ticket->delete();
                $this->_threadRepo->deleteThreads($ticketId);
                break;
            case "mail_agent":
                if (!empty($responseAction['mail_agent']['agent'])
                    &&
                    $responseAction['mail_agent']['agent']['0'] == "agent"
                ) {
                    $agentId = $ticket->getToAgent();
                } else {
                    $agentId = $responseAction['mail_agent']['agent']['0'];
                }
                if ($agentId) {
                    $agent = $this->_userFactory->create()->load($agentId);
                    if (isset($responseAction['mail_agent']['template_id'])) {
                        $agent = $this->_userFactory->create()->load($agentId);
                        $senderData = [
                            'email' => $supportEmail,
                            'name' => $supportName
                        ];
                        $templateId = $responseAction['mail_agent']['template_id'];
                        if ($templateId!="") {
                            $agentEmail = $agent->getEmail();
                            $agentName = $agent->getFirstname()." ".$agent->getLastname();
                            $this->sendMailByTemplateId(
                                $templateId,
                                $emailTempVariables,
                                $senderData,
                                $agentEmail,
                                $agentName,
                                $ticketId,
                                $ticket
                            );
                        }
                    } else {
                        $content = isset($responseAction['mail_agent']['content']
                        ['0'])?$responseAction['mail_agent']['content']['0']:"";
                        $processedSubject = isset($responseAction['mail_agent']
                        ['subject']['0'])?$responseAction['mail_agent']['subject']
                        ['0']:"";
                        $senderData = [
                            'email' => $supportEmail,
                            'name' => $supportName
                        ];
                        $agent = $this->_userFactory->create()->load($agentId);
                        $agentEmail = $agent->getEmail();
                        $agentName = $agent->getFirstname()." 
                        ".$agent->getLastname();
                        $this->sendMailByTemplateContent(
                            $content,
                            $emailTempVariables,
                            $senderData,
                            $agentEmail,
                            $agentName,
                            $ticketId,
                            $ticket,
                            $processedSubject
                        );
                    }
                }
                break;
            case "mail_group":
                $this->mailGroup($responseAction, $ticket);
                break;
            case "mail_customer":
                $customer = $this->_helpdeskCustomerFactory->create()->load($ticket->getCustomerId());
                $supportEmail = $ticket->getEmail();
                $customerName = $customer->getName();
                $customerEmail = $customer->getEmail();
                if (isset($responseAction['mail_customer']['template_id']) &&
                !empty($responseAction['mail_customer']['template_id'])) {
                    $senderData = [
                        'email' => $supportEmail,
                        'name' => $supportName
                    ];
                    $templateId = $responseAction['mail_customer']['template_id'];
                    if ($templateId!="") {
                        $this->sendMailByTemplateId(
                            $templateId,
                            $emailTempVariables,
                            $senderData,
                            $customerEmail,
                            $customerName,
                            $ticketId,
                            $ticket,
                            $actionType
                        );
                    }
                } else {
                    $content = isset($responseAction['mail_customer']['content']
                    ['0'])?$responseAction['mail_customer']['content']['0']:"";
                    $subject = isset($responseAction['mail_customer']['subject']
                    ['0'])?$responseAction['mail_customer']['subject']['0']:"";
                    $senderData = [
                        'email' => $ticket->getEmail(),
                        'name' => $ticket->getFullname()
                    ];

                    $this->sendMailByTemplateContent(
                        $content,
                        $emailTempVariables,
                        $senderData,
                        $customerEmail,
                        $customerName,
                        $ticketId,
                        $ticket,
                        $subject,
                        $actionType
                    );
                }
                break;
        }
    }

    public function mailGroup($responseAction, $ticket)
    {
        if ($responseAction['mail_group']['group']['0'] == "group") {
            $groupId = $ticket->getToGroup();
        } else {
            $groupId = $responseAction['mail_group']['group']['0'];
        }
        if ($groupId) {
            $agentCollection = $this->_agentFactory->create()
            ->getCollection()
                            ->addFieldToFilter(
                                "group_id",
                                ["in"=>$groupId]
                            );
            $agentIds = [];
            foreach ($agentCollection as $row) {
                array_push($agentIds, $row->getUserId());
            }
            if ($responseAction['mail_group']['template_id']) {
                foreach ($agentIds as $agentId) {
                    $agent = $this->_userFactory->create()->load($agentId);
                    $senderData = [
                        'email' => $supportEmail,
                        'name' => $supportName
                    ];
                    $templateId = $responseAction['mail_group']['template_id'];
                    if ($templateId!="") {
                        $agentEmail = $agent->getEmail();
                        $agentName = $agent->getFirstname()." ".$agent->getLastname();
                        $this->sendMailByTemplateId(
                            $templateId,
                            $emailTempVariables,
                            $senderData,
                            $agentEmail,
                            $agentName.
                            $ticketId,
                            $ticket
                        );
                    }
                }
            } else {
                foreach ($agentIds as $agentId) {
                    $content = isset($responseAction['mail_group']
                    ['content']['0'])?$responseAction['mail_group']
                    ['content']['0']:"";
                    $processedSubject = isset($responseAction
                    ['mail_group']['subject']['0'])?$responseAction
                    ['mail_group']['subject']['0']:"";
                    $senderData = [
                        'email' => $supportEmail,
                        'name' => $supportName
                    ];
                    $agent = $this->_userFactory->create()->load($agentId);
                    $agentEmail = $agent->getEmail();
                    $agentName = $agent->getFirstname()." ".$agent->getLastname();
                    $this->sendMailByTemplateContent(
                        $content,
                        $emailTempVariables,
                        $senderData,
                        $agentEmail,
                        $agentName,
                        $ticketId,
                        $ticket,
                        $processedSubject,
                        $actionType
                    );
                }
            }
        }
    }
}
