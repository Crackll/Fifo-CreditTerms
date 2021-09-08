<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml\Edit\Tab;

class AbstractAction extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketsPriorityFactory
     */
    protected $_ticketsPriorityFactory;

    /**
     * @var \Webkul\Helpdesk\Model\TypeFactory $typeFactory
     */
    protected $_typeFactory;

    /**
     * @var \Webkul\Helpdesk\Model\GroupFactory $groupFactory
     */
    protected $_groupFactory;

    /**
     * @var \Webkul\Helpdesk\Model\GroupFactory $agentFactory
     */
    protected $_agentFactory;

    /**
     * @var \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketsStatusFactory
     */
    protected $_ticketsStatusFactory;

    /**
     * @var \Webkul\Helpdesk\Model\EmailTemplateFactory $emailTemplateFactory
     */
    protected $_emailTemplateFactory;

    /**
     * @var \Webkul\Helpdesk\Model\TagFactory $tagFactory
     */
    protected $_tagFactory;

    /**
     * @var \Webkul\Helpdesk\Model\ResponsesFactory $responsesFactory
     */
    protected $_responsesFactory;

    /**
     * @var \Webkul\Helpdesk\Model\EventsFactory $eventsFactory
     */
    protected $_eventsFactory;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Webkul\Helpdesk\Model\SlapolicyFactory
     */
    protected $_slapolicyFactory;

    /**
     * @param \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketsPriorityFactory
     * @param \Webkul\Helpdesk\Model\TypeFactory $typeFactory
     * @param \Webkul\Helpdesk\Model\GroupFactory $groupFactory
     * @param \Webkul\Helpdesk\Model\AgentFactory $agentFactory
     * @param \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketsStatusFactory
     * @param \Webkul\Helpdesk\Model\EmailTemplateFactory $emailTemplateFactory
     * @param \Webkul\Helpdesk\Model\TagFactory $tagFactory
     * @param \Webkul\Helpdesk\Model\ResponsesFactory $responsesFactory
     * @param \Webkul\Helpdesk\Model\EventsFactory $eventsFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config      $wysiwygConfig
     * @param \Webkul\Helpdesk\Model\SlapolicyFactory $slapolicyFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketsPriorityFactory,
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
        \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketsStatusFactory,
        \Webkul\Helpdesk\Model\EmailTemplateFactory $emailTemplateFactory,
        \Webkul\Helpdesk\Model\TagFactory $tagFactory,
        \Webkul\Helpdesk\Model\ResponsesFactory $responsesFactory,
        \Webkul\Helpdesk\Model\EventsFactory $eventsFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Webkul\Helpdesk\Model\SlapolicyFactory $slapolicyFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Helpdesk\Helper\Data $dataHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ticketsPriorityFactory = $ticketsPriorityFactory;
        $this->_typeFactory = $typeFactory;
        $this->_groupFactory = $groupFactory;
        $this->_agentFactory = $agentFactory;
        $this->_ticketsStatusFactory = $ticketsStatusFactory;
        $this->_emailTemplateFactory = $emailTemplateFactory;
        $this->_tagFactory = $tagFactory;
        $this->_responsesFactory = $responsesFactory;
        $this->_eventsFactory = $eventsFactory;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_slapolicyFactory = $slapolicyFactory;
        $this->_userFactory = $userFactory;
        $this->userFactory = $userFactory;
        $this->dataHelper = $dataHelper;
        $this->jsonHelper = $jsonHelper;
        $this->serializer = $serializer;
    }

    public function getCurrentEventData()
    {
        $eventId = $this->getRequest()->getParam('id');
        $eventModel = $this->_eventsFactory->create()->load($eventId);
        return $eventModel;
    }

    //get Current response data
    public function getCurrentResponseData()
    {
        $responseId = $this->getRequest()->getParam('id');
        $responseModel = $this->_responsesFactory->create()->load($responseId);
        return $responseModel;
    }

    //get Current response data
    public function getCurrentSlaPolicy()
    {
        $slaId = $this->getRequest()->getParam('id');
        $slaModel = $this->_slapolicyFactory->create()->load($slaId);
        return $slaModel;
    }

    //get All Enable Ticket priority
    public function getTicketsPriority()
    {
        return $this->_ticketsPriorityFactory->create()->toOptionArray();
    }

    //get All Enable Ticket type
    public function getTicketsType()
    {
        return $this->_typeFactory->create()->toOptionArray();
    }

    //get All Enable Ticket group
    public function getTicketsGroup()
    {
        return $this->_groupFactory->create()->toOptionArray();
    }

    //get All Agent collection
    public function getAgentCollection()
    {
        $ticketAgentColl = $this->_agentFactory->create()->getCollection();
        $agentids = [];
        foreach ($ticketAgentColl as $agent) {
            $agentids[] = $agent->getUserId();
        }
        $userColl = $this->_userFactory->create()->getCollection()
                           ->addFieldToFilter("main_table.user_id", ["in"=>$agentids]);
        return $userColl;
    }

    //get All Tickets Status
    public function getTicketsStatus()
    {
        return $this->_ticketsStatusFactory->create()->toOptionArray();
    }

    public function getTicketTemplateCollection()
    {
        $helpdeslTemp = $this->_emailTemplateFactory->create()->getCollection();
        $joinTable2 = $helpdeslTemp->getTable('email_template');
        $helpdeslTemp->getSelect()->join(
            $joinTable2.' as et',
            'main_table.template_id = et.template_id'
        );
        return $helpdeslTemp;
    }

    public function getActionType()
    {
        $types = [
            ["value" => "priority","label" => __('Set Priority As')],
            ["value" => "type","label" => __('Set Type As')],
            ["value" => "status","label" => __('Set Status As')],
            ["value" => "tag","label" => __('Add Tag')],
            ["value" => "cc","label" => __('Add CC')],
            ["value" => "bcc","label" => __('Add BCC')],
            ["value" => "note","label" => __('Add Note')],
            ["value" => "assign_agent","label" => __('Assign To Agent')],
            ["value" => "assign_group","label" => __('Assign To Group')],
            ["value" => "mail_agent","label" => __('Send Mail To Agent')],
            ["value" => "mail_group","label" => __('Send Mail To Group')],
            ["value" => "mail_customer","label" => __('Send Mail To Customer')],
            ["value" => "delete_ticket","label" => __('Delete Ticket')],
            ["value" => "mark_spam","label" => __('Mark Spam')],
        ];
        return $types;
    }

    //get All Tag collection
    public function getAllTagCollection()
    {
        return $this->_tagFactory->create()->getCollection();
    }

    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        return $config;
    }

    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }
}
