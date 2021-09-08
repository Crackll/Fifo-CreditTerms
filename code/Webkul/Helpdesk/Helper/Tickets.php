<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Helper;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Webkul Helpdesk Helper Data.
 */
class Tickets extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;
    protected $_customAttribute;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\Model\Auth\Session $authSession,
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Webkul\Helpdesk\Model\TicketsPriorityFactory $priorityFactory,
        \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketsStatusFactory,
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Webkul\Helpdesk\Model\TicketslaRepository $ticketslaRepo,
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketFactory,
        \Webkul\Helpdesk\Model\TicketsAttributeValueFactory $ticketsAttrValueFactory,
        \Webkul\Helpdesk\Model\MailfetchFactory $mailfetchFactory,
        \Webkul\Helpdesk\Model\CustomerFactory $customerFactory,
        \Webkul\Helpdesk\Model\TicketdraftFactory $ticketdraftFactory,
        \Webkul\Helpdesk\Model\ResponsesFactory $responsesFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Customer\Model\Session $mageCustomerSession,
        \Magento\Framework\Session\SessionManager $coreSession,
        \Webkul\Helpdesk\Helper\Data $helper,
        \Webkul\Helpdesk\Model\Eav\CustomAttributeFactory $customAttribute
    ) {
        parent::__construct($context);
        $this->_authSession = $authSession;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_threadFactory = $threadFactory;
        $this->_priorityFactory = $priorityFactory;
        $this->_ticketsStatusFactory = $ticketsStatusFactory;
        $this->_typeFactory = $typeFactory;
        $this->_ticketslaRepo = $ticketslaRepo;
        $this->_agentFactory = $agentFactory;
        $this->_groupFactory = $groupFactory;
        $this->_ticketsAttrValueFactory = $ticketsAttrValueFactory;
        $this->_mailfetchFactory = $mailfetchFactory;
        $this->_customerFactory = $customerFactory;
        $this->_ticketdraftFactory = $ticketdraftFactory;
        $this->_responsesFactory = $responsesFactory;
        $this->_userFactory = $userFactory;
        $this->_mageCustomerSession = $mageCustomerSession;
        $this->_coreSession = $coreSession;
        $this->_helper = $helper;
        $this->_customAttribute = $customAttribute;
    }

    /**
     * getTotalThreads This function returns total thread count
     * @return String total thread count
     */
    public function getTotalThreads($ticketId)
    {
        $collection = $this->_threadFactory->create()->getCollection()
                            ->addFieldToFilter("ticket_id", $ticketId)
                            ->addFieldToFilter("thread_type", ["neq"=>"create"]);
        return count($collection);
    }

    /**
     * getCurrentAgentId This function returns current user id
     * @return int
     */
    public function getCurrentAgentId()
    {
        return $userId = $this->_authSession->getUser()->getId();
    }

    //get Current ticket priority name
    public function getTicketPriorityName($ticketId)
    {
        $priorityName = "";
        $ticket = $this->_ticketsFactory->create()->load($ticketId);
        $priorityArr = $this->_priorityFactory->create()->toOptionArray();
        foreach ($priorityArr as $priority) {
            if ($ticket->getPriority()==$priority['value']) {
                $priorityName = $priority['label'];
            }
        }
        return $priorityName;
    }

    //get Current ticket status name
    public function getTicketStatusName($ticketId)
    {
        $statusName = "";
        $ticket = $this->_ticketsFactory->create()->load($ticketId);
        $statusArr = $this->_ticketsStatusFactory->create()->toOptionArray();
        foreach ($statusArr as $status) {
            if ($ticket->getStatus()==$status['value']) {
                $statusName = $status['label'];
            }
        }
        return $statusName;
    }

    //get Current ticket Type name
    public function getTicketTypeName($ticketId)
    {
        $typeName = "";
        $ticket = $this->_ticketsFactory->create()->load($ticketId);
        $typeArr = $this->_typeFactory->create()->toOptionArray();
        foreach ($typeArr as $type) {
            if ($ticket->getType()==$type['value']) {
                $typeName = $type['label'];
            }
        }
        return $typeName;
    }

    /**
     * getAgentOffset Return agent timesatmp offset
     * @return Float $offset Agent timesatmp offset
     */
    public function getAgentOffset()
    {
        // $offset = "";
        /**Fix: If offset is not it will throw error non-numeric value on viewreply.phtml previously */
        $offset = 0;
        $userId = $this->getCurrentAgentId();
        $agent = $this->_agentFactory->create()->getCollection()
                                ->addFieldToFilter("user_id", ["eq" => $userId])
                                ->getFirstItem();
        if ($agent->getTimezone()) {
            $offset = timezone_offset_get(timezone_open($agent->getTimezone()), new \DateTime());
        }
        return $offset;
    }

    /**
     * getTicketResponseTime
     * @param $ticketId
     * @return String Resolve time
     */
    public function getTicketResponseTime($ticketId)
    {
        return $this->_ticketslaRepo->getTicketResponseTime($ticketId);
    }

    /**
     * getTicketResolveTime
     * @param $ticketId
     * @return String Resolve time
     */
    public function getTicketResolveTime($ticketId)
    {
        return $this->_ticketslaRepo->getTicketResolveTime($ticketId);
    }

    //get Current ticket Type name
    public function getTicketAgentName($ticketId)
    {
        $agentName = "";
        $ticket = $this->_ticketsFactory->create()->load($ticketId);
        $agentArr = $this->_agentFactory->create()->toOptionArray();
        foreach ($agentArr as $agent) {
            if ($ticket->getToAgent()==$agent['value']) {
                $agentName = $agent['label'];
            }
        }
        return $agentName;
    }

    //get Current ticket Type name
    public function getTicketGroupName($ticketId)
    {
        $groupName = "";
        $ticket = $this->_ticketsFactory->create()->load($ticketId);
        $groupArr = $this->_groupFactory->create()->toOptionArray();
        foreach ($groupArr as $group) {
            if ($ticket->getToAgent()==$group['value']) {
                $groupName = $group['label'];
            }
        }
        return $groupName;
    }

    /**
     * getTicketAttributeDetails Retun ticket attribute details
     * @param $ticketId
     * @return Object Ticket attribute details
     */
    public function getTicketAttributeDetails($ticketId)
    {
        return $this->_ticketsAttrValueFactory->create()->getCollection()
                    ->addFieldToFilter("ticket_id", ["eq"=>$ticketId]);
    }

    /**
     * getCreateTypeTreadDetails This function returns create type thread
     * @return Object create type thread
     */
    public function getCreateTypeTreadDetails($ticketId)
    {
        $collection = $this->_threadFactory->create()->getCollection()
                            ->addFieldToFilter("ticket_id", $ticketId);
        return $collection->getLastItem();
    }

    /**
     * getMaxPages This function returns the max pages count
     * @param $threadLimit
     * @return Int max pages count
     */
    public function getMaxPages($threadLimit, $ticketId)
    {
        $collection = $this->_threadFactory->create()->getCollection()
                            ->addFieldToFilter("ticket_id", $ticketId)
                            ->addFieldToFilter("thread_type", ["neq"=>"create"])
                            ->setCurPage(1)->setPageSize($threadLimit);
        return $collection->getLastPageNumber();
    }

    /**
     * getMailFetchCollection This function returns mails
     * @param $threadId
     * @return Object mailfetch
     */
    public function getMailFetchCollection($threadId)
    {
        return $this->_mailfetchFactory->create()->getCollection()
                            ->addFieldToFilter("thread_id", $threadId)
                            ->getFirstItem();
    }

    /**
     * getCurrentAgent This function returns loggin admin user
     * @return Object user
     */
    public function getCurrentAgent()
    {
        return $this->_authSession->getUser();
    }

    /**
     * getDraftContent This function returns agent draft content
     * @return String customer draft content
     */
    public function getDraftContent($fieldType, $ticketId)
    {
        if ($this->_helper->isAdmin()) {
            $userId = $this->getCurrentAgentId();
            $usertype = "admin";
        } else {
            $userId = $this->getTsCustomerId();
            $usertype = "customer";
        }

        $ticketCollection = $this->_ticketdraftFactory->create()->getCollection()
                                ->addFieldToFilter("ticket_id", ["eq"=>$ticketId])
                                ->addFieldToFilter("user_id", ["eq"=>$userId])
                                ->addFieldToFilter("user_type", ["eq"=>$usertype])
                                ->addFieldToFilter("field", ["eq"=>$fieldType]);
        $content = $ticketCollection->getFirstItem()->getContent();
        if ($usertype=="admin") {
            if ($content == "") {
                if ($fieldType == "reply" || $fieldType == "forward") {
                    $agent = $this->_agentFactory->create()->getCollection()
                                    ->addFieldToFilter("user_id", $userId)
                                    ->getFirstItem();
                    if ($agent->getSignature() != "") {
                        $content = "<br/><br/><br/><br/>".nl2br($agent->getSignature());
                    }
                }
            }
        }
        return $content;
    }

    /**
     * getAllEnableResponses This function returns responses
     * @return object response collection
     */
    public function getAllEnableResponses()
    {
        return $this->_responsesFactory->create()->getCollection()
                                ->addFieldToFilter("status", ["eq"=>1]);
    }

    /**
     * getGroupDataById This function returns group
     * @param $groupId
     * @return object group model
     */
    public function getGroupDataById($groupId)
    {
        return $this->_groupFactory->create()->load($groupId);
    }

    /**
     * getHelpdeskCustomerById This function returns helpdesk Customer
     * @param $customerId
     * @return object customer model
     */
    public function getHelpdeskCustomerById($customerId)
    {
        return $this->_customerFactory->create()->load($customerId);
    }

    /**
     * getHelpdeskCustomerByMageCustomerId This function returns helpdesk Customer
     * @param $customerId
     * @return object customer model
     */
    public function getHelpdeskCustomerByMageCustomerId($mageCustomerId)
    {
        return $this->_customerFactory->create()->getCollection()
                    ->addFieldToFilter("customer_id", ["eq"=>$mageCustomerId])
                    ->getFirstItem();
    }

     /**
      * getHelpdeskCustomerByMageCustomerId This function returns helpdesk Customer
      * @param $customerId
      * @return object customer model
      */
    public function getHelpdeskCustomerByMageCustomerEmail($email)
    {
        return $this->_customerFactory->create()->getCollection()
                    ->addFieldToFilter("email", ["eq"=>$email])
                    ->getFirstItem();
    }

    /**
     * getAdminUserById This function return admin user data
     * @param $userId
     * @return object customer model
     */
    public function getAdminUserById($userId)
    {
        return $this->_userFactory->create()->load($userId);
    }

    //Check customer is looged in
    public function getCustomerSession()
    {
        return $this->_mageCustomerSession;
    }

    /**
     * getTsCustomerId Returns logged in customer id
     * @return Int $userId Customer id
     */
    public function getTsCustomerId()
    {
        $userId = 0;
        if ($this->_mageCustomerSession->isLoggedIn()) {
            $userId = $this->_mageCustomerSession->getCustomerId();
            $customer = $this->_customerFactory->create()->getCollection()
                        ->addFieldToFilter("customer_id", $userId)
                        ->getFirstItem();
            $userId = $customer->getId();
        } else {
            $data = $this->_coreSession->getTsCustomer();
            $userId = $data['customer_id'] ?? 0;
        }
        return $userId;
    }

    /**
     * getTsCustomerData Returns guest customer data
     * @return Array
     */
    public function getTsCustomerData()
    {
        return $this->_coreSession->getTsCustomer();
    }

    /**
     * unSetTsCustomerData
     */
    public function unSetTsCustomerData()
    {
        $this->_coreSession->unsTsCustomer();
    }

    public function getCustomAttributeDependency($type)
    {
        try {
            return $this->_customAttribute->create()->getCollection()
                    ->addFieldToFilter("field_dependency", ["eq"=>$type]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
