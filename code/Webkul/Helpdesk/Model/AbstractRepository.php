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
use \Magento\Framework\Filesystem\Driver\File;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class AbstractRepository
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
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\Helpdesk\Model\CustomerFactory
     */
    protected $_helpdeskCustomerFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Webkul\Helpdesk\Model\ConnectEmailFactory
     */
    protected $_connectemailFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Webkul\Helpdesk\Model\TicketsAttributeValueRepository
     */
    protected $_ticketsAttributeValueRepo;

    /**
     * @var \Webkul\Helpdesk\Model\ThreadFactory
     */
    protected $_threadFactory;

    /**
     * @var \Webkul\Helpdesk\Model\EventsRepository
     */
    protected $_eventsRepo;

    /**
     * TicketsRepository constructor.
     * @param \Webkul\Helpdesk\Helper\Data $helper
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Helpdesk\Model\CustomerFactory $helpdeskCustomerFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\Helpdesk\Model\ConnectEmailFactory $connectemailFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Webkul\Helpdesk\Model\TicketsAttributeValueRepository $ticketsAttributeValueRepo
     * @param \Webkul\Helpdesk\Model\ThreadFactory $threadFactory
     * @param \Webkul\Helpdesk\Model\EventsRepository $eventsRepo
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Webkul\Helpdesk\Helper\Data $helper,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Helpdesk\Model\CustomerFactory $helpdeskCustomerFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\Helpdesk\Model\ConnectEmailFactory $connectemailFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Model\TicketsAttributeValueRepository $ticketsAttributeValueRepo,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\ActivityFactory $activityFactory,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\TagFactory $tagFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketpriorityFactory,
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketstatusFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Helpdesk\Model\CustomerOrganizationFactory $custOrgFactory,
        \Webkul\Helpdesk\Model\EventsFactory $eventsFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Helpdesk\Model\ResponsesRepository $responseRepo,
        \Webkul\Helpdesk\Model\EmailTemplateRepository $emailTemplateRepo,
        \Webkul\Helpdesk\Model\ThreadRepository $threadRepo,
        \Webkul\Helpdesk\Model\MailfetchFactory $mailfetchFactory,
        \Webkul\Helpdesk\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
        \Webkul\Helpdesk\Model\TicketsCustomAttributesFactory $ticketsCustomAttributesFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Webkul\Helpdesk\Model\TicketsAttributeValueFactory $ticketsAttributeValueFactory,
        File $file,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_helper = $helper;
        $this->_sessionManager = $sessionManager;
        $this->_customerSession = $customerSession;
        $this->_helpdeskCustomerFactory = $helpdeskCustomerFactory;
        $this->_customerFactory = $customerFactory;
        $this->_connectemailFactory = $connectemailFactory;
        $this->_authSession = $authSession;
        $this->_ticketsAttributeValueRepo = $ticketsAttributeValueRepo;
        $this->_threadFactory = $threadFactory;
        $this->_eventsRepo = $eventsRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_activityFactory = $activityFactory;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_tagFactory = $tagFactory;
        $this->_groupFactory = $groupFactory;
        $this->_ticketpriorityFactory = $ticketpriorityFactory;
        $this->_typeFactory = $typeFactory;
        $this->_ticketstatusFactory = $ticketstatusFactory;
        $this->_userFactory = $userFactory;
        $this->_custOrgFactory = $custOrgFactory;
        $this->_eventsFactory = $eventsFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->_date = $date;
        $this->_responseRepo = $responseRepo;
        $this->_emailTemplateRepo = $emailTemplateRepo;
        $this->_threadRepo = $threadRepo;
        $this->_mailfetchFactory = $mailfetchFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_emailTemplateFactory = $emailTemplateFactory;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_agentFactory = $agentFactory;
        $this->_ticketsCustomAttributesFactory = $ticketsCustomAttributesFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->_ticketsAttributeValueFactory = $ticketsAttributeValueFactory;
        $this->_file = $file;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }
}
