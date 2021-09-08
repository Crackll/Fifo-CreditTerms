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
class EventsRepository implements \Webkul\Helpdesk\Api\EventsRepositoryInterface
{
    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $_sessionManager;

    /**
     * @var \Webkul\Helpdesk\Model\EventsFactory
     */
    protected $_eventsFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @var \Webkul\Helpdesk\Model\TicketsFactory
     */
    protected $_ticketsFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Webkul\Helpdesk\Model\CustomerOrganizationFactory
     */
    protected $_custOrgFactory;

    /**
     * @var \Webkul\Helpdesk\Model\ResponsesRepository
     */
    protected $_responseRepo;

    /**
     * TicketsRepository constructor.
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Webkul\Helpdesk\Model\EventsFactory $eventsFactory,
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper,
     * @param \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
     * @param \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date,
     * @param \Webkul\Helpdesk\Model\CustomerOrganizationFactory $custOrgFactory,
     * @param \Webkul\Helpdesk\Model\ResponsesRepository $responseRepo
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Webkul\Helpdesk\Model\EventsFactory $eventsFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Helpdesk\Model\CustomerOrganizationFactory $custOrgFactory,
        \Webkul\Helpdesk\Model\ResponsesRepository $responseRepo,
        \Webkul\Helpdesk\Helper\Tickets $ticketHelper
    ) {
        $this->_sessionManager = $sessionManager;
        $this->_eventsFactory = $eventsFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_date = $date;
        $this->_custOrgFactory = $custOrgFactory;
        $this->_responseRepo = $responseRepo;
        $this->_ticketHelper = $ticketHelper;
    }

    /**
     * checkTicketEvent Record the ticket event
     * @param String $actionType Event Type
     * @param Int $ticketId Ticket Id
     * @param Sting $from From
     * @param Sting $to to
     */
    public function checkTicketEvent($actionType, $ticketId, $from = null, $to = null)
    {
        try {
            $collection = $this->_eventsFactory->create()->getCollection()->addFieldToFilter("status", ["eq"=>1]);
            $isAlreadyExecuted = $this->_sessionManager->getIsAlreadyExecuted();
            if (!$isAlreadyExecuted) {
                $this->_sessionManager->setIsAlreadyExecuted(1);
                foreach ($collection as $event) {
                    $events = json_decode($event->getEvent(), true);
                    $actionTypes = isset($events['action-type'])?$events['action-type']:"";
                    if ($actionTypes != "" && in_array($actionType, $actionTypes)) {
                        $this->conditionCheck($actionType, $from, $to, $events, $ticketId, $event);
                    }
                }
            }
            $this->_sessionManager->unsIsAlreadyExecuted();
            $this->_sessionManager->unsTicketReplyInfo();
        } catch (\Exception $e) {
            $this->_sessionManager->unsIsAlreadyExecuted();
            $this->_sessionManager->unsTicketReplyInfo();
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }
    }

    /**
     * checkTicketConditionForSla Check ticket condition for ticket rules
     * @param int $ticketId Ticket Id
     * @param int $type Event Id
     */
    public function checkTicketCondition($ticketId, $eventId)
    {
        try {
            $ticket = $this->_ticketsFactory->create()->load($ticketId);
            $collection = $this->_eventsFactory->create()->getCollection()
                                    ->addFieldToFilter("entity_id", ["eq"=>$eventId]);
            foreach ($collection as $rule) {
                $oneCondition = json_decode($rule->getOneConditionCheck(), true);
                $actions = $rule->getActions();
                $flag = false;
                $count = 0;
                $this->checkActionType($oneCondition, $flag, $rule, $ticket, $ticketId);
                if ($count == 0) {
                    $this->_responseRepo->applyResponseToTicket($ticketId, $actions);
                }
            }
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__('Unable to manage events'), $e);
        }
    }

    /**
     * checkCondition Rules for condition check
     * @param String $condition Condition
     * @param String $haystack Haystack
     * @param String $needle Needle
     * @return Booleam
     */
    public function checkCondition($condition, $haystack, $needle)
    {
        switch ($condition) {
            case 'is':
                if ($haystack == $needle) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'isNot':
                if ($haystack == $needle) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'isContains':
                if ($haystack == $needle) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'isNotContains':
                if (strpos($haystack, $needle) !== false) {
                    return false;
                } else {
                    return true;
                }
                break;
            case 'startWith':
                return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !==
                false;
            case 'endWith':
                return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0
                && strpos($haystack, $needle, $temp) !== false);
            case 'before':
                $createdTimeStamp = $this->_date->gmtTimestamp('Y-m-d', strtotime($haystack));
                $conditionTimeStamp = $this->_date->gmtTimestamp('Y-m-d', strtotime($needle." 
                23:59:59"));
                if ($createdTimeStamp < $conditionTimeStamp) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'beforeOn':
                $createdTimeStamp = $this->_date->gmtTimestamp('Y-m-d', strtotime($haystack));
                $conditionTimeStamp = $this->_date->gmtTimestamp('Y-m-d', strtotime($needle." 
                23:59:59"));
                if ($createdTimeStamp < $conditionTimeStamp || $createdTimeStamp ==
                $conditionTimeStamp) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'after':
                $createdTimeStamp = $this->_date->gmtTimestamp('Y-m-d', strtotime($haystack));
                $conditionTimeStamp = $this->_date->gmtTimestamp('Y-m-d', strtotime($needle." 
                23:59:59"));
                if ($createdTimeStamp > $conditionTimeStamp) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'afterOn':
                $createdTimeStamp = $this->_date->gmtTimestamp('Y-m-d', strtotime($haystack));
                $conditionTimeStamp = $this->_date->gmtTimestamp('Y-m-d', strtotime($needle." 
                23:59:59"));
                if ($createdTimeStamp > $conditionTimeStamp || $createdTimeStamp ==
                $conditionTimeStamp) {
                    return true;
                } else {
                    return false;
                }
                break;
        }
    }

    public function conditionCheck($actionType, $from, $to, $events, $ticketId, $event)
    {
        switch ($actionType) {
            case 'priority':
                if ($events['priority']['from'] == $from && $events['priority']['to'] == $to) {
                    $this->checkTicketCondition($ticketId, $event->getId());
                }
                break;
            case 'status':
                if ($events['status']['from'] == $from && $events['status']['to'] == $to) {
                    $this->checkTicketCondition($ticketId, $event->getId());
                }
                break;
            case 'agent':
                if ($events['agent']['from'] == $from && $events['agent']['to'] == $to) {
                    $this->checkTicketCondition($ticketId, $event->getId());
                }
                break;
            case 'group':
                if ($events['group']['from'] == $from && $events['group']['to'] == $to) {
                    $this->checkTicketCondition($ticketId, $event->getId());
                }
                break;
            case 'type':
                if ($events['type']['from'] == $from && $events['type']['to'] == $to) {
                    $this->checkTicketCondition($ticketId, $event->getId());
                }
                break;
            case 'note':
                if ($from == "note") {
                    $from = "private";
                }
                if ($events['note']['added'] == $from) {
                    $data = [];
                    $data['type'] = 'forward';
                    $this->_sessionManager->setTicketReplyInfo($data);
                    $this->checkTicketCondition($ticketId, $event->getId());
                } elseif ($events['note']['added'] == "any") {
                    $this->checkTicketCondition($ticketId, $event->getId());
                }
                break;
            case 'reply':
                $data = [];
                $data['type'] = 'reply';
                $data['by'] = $from;
                if ($events['reply']['added'] == $from) {
                    $this->_sessionManager->setTicketReplyInfo($data);
                    $this->checkTicketCondition($ticketId, $event->getId());
                }
                break;
            case 'ticket':
                if ($events['ticket']['from'] == $from) {
                    $this->checkTicketCondition($ticketId, $event->getId());
                }
                break;
        }
    }

    public function checkActionType($oneCondition, $flag, $rule, $ticket, $ticketId)
    {
        if (isset($oneCondition['action-type'])) {
            foreach ($oneCondition['action-type'] as $actionType) {
                $count = 1;
                switch ($actionType) {
                    case 'from':
                        $flag = $this->checkCondition($oneCondition['from']
                        ['match_condition'], $ticket->getFrom(), $oneCondition['from']
                        ['match']);
                        break;
                    case 'to':
                        $flag = $this->checkCondition($oneCondition['to']
                        ['match_condition'], $ticket->getTo(), $oneCondition['to']
                        ['match']);
                        break;
                    case 'subject':
                        $flag = $this->checkCondition($oneCondition['subject']
                        ['match_condition'], $ticket->getSubject(), $oneCondition
                        ['subject']['match']);
                        break;
                    case 'description':
                        $flag = $this->checkCondition($oneCondition['description']
                        ['match_condition'], $ticket->getSubject(), $oneCondition
                        ['description']['match']);
                        break;
                    case 'subject_description':
                        $flag = $this->checkCondition(
                            $oneCondition
                            ['subject_description']['match_condition'],
                            $ticket->getSubject(),
                            $oneCondition['subject_description']
                            ['match']
                        );
                        break;
                    case 'priority':
                        $flag = $this->checkCondition($oneCondition['priority']
                        ['match_condition'], $ticket->getPriority(), $oneCondition
                        ['priority']['match']);
                        break;
                    case 'source':
                        $flag = $this->checkCondition($oneCondition['source']
                        ['match_condition'], $ticket->getSource(), $oneCondition
                        ['source']['match']);
                        break;
                    case 'created':
                        $flag = $this->checkCondition($oneCondition['created']
                        ['match_condition'], $ticket->getCreatedAt(), $oneCondition
                        ['created']['match']);
                        break;
                    case 'agent':
                        $flag = $this->checkCondition($oneCondition['agent']
                        ['match_condition'], $ticket->getToAgent(), $oneCondition
                        ['agent']['match']);
                        break;
                    case 'group':
                        $flag = $this->checkCondition($oneCondition['group']
                        ['match_condition'], $ticket->getToGroup(), $oneCondition
                        ['group']['match']);
                        break;
                    case 'customer_email':
                        $flag = $this->checkCondition($oneCondition['customer_email']
                        ['match'], $ticket->getEmail(), $oneCondition
                        ['customer_email']['match']);
                        break;
                    case 'customer_name':
                        $flag = $this->checkCondition($oneCondition['customer_name']
                        ['match_condition'], $ticket->getFullname(), $oneCondition
                        ['customer_name']['match']);
                        break;
                    case 'organization_name':
                        $orgName = "";
                        $helpdeskCustomer =
                        $this->_ticketHelper->getHelpdeskCustomerById($ticket->getCustomerId());
                        if ($helpdeskCustomer->getOrganizations()!="") {
                            $org = $this->_custOrgFactory->create()->load(
                                $helpdeskCustomer->getOrganizations()
                            );
                            $orgName = $org->getName();
                        }

                        if ($orgName!="") {
                            $flag = $this->checkCondition(
                                $oneCondition
                                ['organization_name']['match_condition'],
                                $orgName,
                                $oneCondition['organization_name']['match']
                            );
                        }
                        break;
                    case 'organization_domain':
                        $orgDomain = "";
                        $helpdeskCustomer =
                        $this->_ticketHelper->getHelpdeskCustomerById($ticket->getCustomerId());
                        if ($helpdeskCustomer->getOrganizations()!="") {
                            $org = $this->_custOrgFactory->create()->load(
                                $helpdeskCustomer->getOrganizations()
                            );
                            $orgDomain = $org->getDomain();
                        }
                        if ($orgDomain!="") {
                            $flag = $this->checkCondition(
                                $oneCondition
                                ['organization_domain']['match_condition'],
                                $orgDomain,
                                $oneCondition['organization_domain']['match']
                            );
                        }
                        break;
                }
                $this->applyResponseToTicket($flag, $ticketId, $actionType);
                
            }
        }
        $this->checkIfNotFlag($flag, $rule);
    }

    public function applyResponseToTicket($flag, $ticketId, $actionType)
    {
        if ($flag) {
            $this->_responseRepo->applyResponseToTicket($ticketId, $actions);
        }
    }

    public function checkIfNotFlag($flag, $rule)
    {
        if (!$flag) {
            $allCondition = json_decode($rule->getAllConditionCheck(), true);
            $flag = false;
            if (isset($allCondition['action-type'])) {
                foreach ($allCondition['action-type'] as $actionType) {
                    $count = 1;
                    $this->checkActionCondition($actionType);
                    if (!$flag) {
                        break;
                    }
                }
            }
            if ($flag) {
                $this->_responseRepo->applyResponseToTicket($ticketId, $actions);
            }
        }
    }

    public function checkActionCondition($actionType)
    {
        switch ($actionType) {
            case 'from':
                $flag = $this->checkCondition($allCondition['from']
                ['match_condition'], $ticket->getFrom(), $allCondition
                ['from']['match']);
                break;
            case 'to':
                $flag = $this->checkCondition($allCondition['to']
                ['match_condition'], $ticket->getTo(), $allCondition['to']
                ['match']);
                break;
            case 'subject':
                $flag = $this->checkCondition($allCondition['subject']
                ['match_condition'], $ticket->getSubject(), $allCondition
                ['subject']['match']);
                break;
            case 'description':
                $flag = $this->checkCondition($allCondition['description']
                ['match_condition'], $ticket->getSubject(), $allCondition
                ['description']['match']);
                break;
            case 'subject_description':
                $flag = $this->checkCondition(
                    $allCondition
                    ['subject_description']['match_condition'],
                    $ticket->getSubject(),
                    $allCondition
                    ['subject_description']['match']
                );
                break;
            case 'priority':
                $flag = $this->checkCondition($allCondition['priority']
                ['match_condition'], $ticket->getPriority(), $allCondition
                ['priority']['match']);
                break;
            case 'status':
                $flag = $this->checkCondition($allCondition['status']
                ['match_condition'], $ticket->getStatus(), $allCondition
                ['status']['match']);
                break;
            case 'source':
                $flag = $this->checkCondition($allCondition['source']
                ['match_condition'], $ticket->getSource(), $allCondition
                ['source']['match']);
                break;
            case 'created':
                $flag = $this->checkCondition(
                    $allCondition['created']
                    ['match_condition']." 23:59:59",
                    $ticket->getCreatedAt(),
                    $allCondition['created']['match']
                );
                break;
            case 'agent':
                $flag = $this->checkCondition($allCondition['agent']
                ['match_condition'], $ticket->getToAgent(), $allCondition
                ['agent']['match']);
                break;
            case 'group':
                $flag = $this->checkCondition($allCondition['group']
                ['match_condition'], $ticket->getToGroup(), $allCondition
                ['group']['match']);
                break;
            case 'customer_email':
                $flag = $this->checkCondition(
                    $allCondition
                    ['customer_email']['match_condition'],
                    $ticket->getEmail(),
                    $allCondition['customer_email']['match']
                );
                break;
            case 'customer_name':
                $flag = $this->checkCondition($allCondition
                ['customer_name']['match_condition'], $ticket->getFullname(
                ), $allCondition['customer_name']['match']);
                break;
            case 'organization_name':
                $orgName = "";
                $helpdeskCustomer =
                $this->_ticketHelper->getHelpdeskCustomerById($ticket->getCustomerId());
                if ($helpdeskCustomer->getOrganizations()!="") {
                    $org = $this->_custOrgFactory->create()->load(
                        $helpdeskCustomer->getOrganizations()
                    );
                    $orgName = $org->getName();
                }

                if ($orgName!="") {
                    $flag = $this->checkCondition(
                        $oneCondition
                        ['organization_name']['match_condition'],
                        $orgName,
                        $oneCondition['organization_name']['match']
                    );
                }
                break;
            case 'organization_domain':
                $orgDomain = "";
                $organizationColl = $this->_custOrgFactory->create()
                ->getCollection();
                foreach ($organizationColl as $org) {
                    $isCustomer =
                    $this->_ticketsHelper->isCustomerOrganization(
                        $org,
                        $ticket->getCustomerId()
                    );
                    if ($isCustomer) {
                        $orgDomain = $org->getDomain();
                        break;
                    }
                }
                if ($orgDomain!="") {
                    $flag = $this->checkCondition(
                        $oneCondition
                        ['organization_domain']['match_condition'],
                        $orgDomain,
                        $oneCondition['organization_domain']
                        ['match']
                    );
                }
        }
    }
}
