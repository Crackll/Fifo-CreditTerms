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
use \Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class TicketsRepository implements \Webkul\Helpdesk\Api\TicketsRepositoryInterface
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
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

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
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\SlaRepository $slaRepo
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
        $this->_ticketsFactory = $ticketsFactory;
        $this->_slaRepo = $slaRepo;
    }

    /**
     * Create product
     *
     * @param mixed $wholedata
     * @return int $ticketId
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function createTicket($wholedata)
    {
        try {
            $error = $this->dataValidate($wholedata);
            if (count($error)>0) {
                throw new InputException(__($error['msg']));
            }
            $tsCustomerId = $wholedata['customer_id'] ?? 0;
            $websiteId = $this->_helper->getDefaultWebsiteId();
            if (!isset($wholedata['status'])) {
                $wholedata['status'] = $this->_helper->getTicketDefaultStatus();
            }
            if ($wholedata['source'] == "email") {
                $connectEmail = $this->_connectemailFactory->create()->load($wholedata['connect_email_id']);
                $wholedata['to_group'] = $connectEmail->getDefaultGroup();
                $wholedata['priority'] = $connectEmail->getDefaultPriority();
                $wholedata['type'] = $connectEmail->getDefaultType();
                $ticketSystemCustomer = $this->_helpdeskCustomerFactory->create()->getCollection()
                                        ->addFieldToFilter("email", ["eq"=>$wholedata['email']]);

                if (count($ticketSystemCustomer)) {
                    $customer = $this->_customerFactory->create()->setWebsiteId($websiteId);
                    $customer->loadByEmail($wholedata['email']);
                    foreach ($ticketSystemCustomer as $value) {
                        if ($customer->getId()) {
                            $value->setCustomerId($customer->getId());
                            $value->save();
                        }
                        $tsCustomerId = $value->getId();
                        $wholedata['email'] = $value->getEmail();
                        $wholedata['fullname'] = $value->getName();
                    }
                } else {
                    $customer = $this->_customerFactory->create()->setWebsiteId($websiteId);
                    $customer->loadByEmail($wholedata['email']);
                    $ticketSystemCustomerModel = $this->_helpdeskCustomerFactory->create();
                    if ($customer->getId()) {
                        $ticketSystemCustomerModel->setCustomerId($customer->getId());
                    }
                    $ticketSystemCustomerModel->setName($wholedata['fullname']);
                    $ticketSystemCustomerModel->setEmail($wholedata['email']);
                    $saved = $ticketSystemCustomerModel->save();
                    $tsCustomerId = $saved->getId();
                }
            } else {
                if (!isset($wholedata['to_group'])) {
                    $wholedata['to_group'] = $this->_helper->getTicketDefaultGroup();
                }
                if (!isset($wholedata['priority'])) {
                    $wholedata['priority'] = $this->_helper->getTicketDefaultPriority();
                }
                $wholedata = $this->checkCustomer($wholedata, $websiteId);
            }

            $wholedata['customer_id'] = $tsCustomerId;
            $ticket = $this->_ticketsFactory->create();
            $ticket->setData($wholedata);
            $id = $ticket->save()->getId();
            $this->_ticketsAttributeValueRepo->saveTicketAttributeValues($id, $wholedata);
            $this->_eventsRepo->checkTicketEvent("ticket", $id, "created");
            $this->_slaRepo->checkTicketConditionForSla($id);
            return $id;
        } catch (InputException $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new InputException(__($e->getMessage()), $e);
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__('Unable to save ticket'), $e);
        }
    }

    //validate ticket data
    public function dataValidate($wholeData)
    {
        $error = [];
        foreach ($wholeData as $key => $value) {
            switch ($key) {
                case 'fullname':
                    if (trim($value)=="") {
                        $error['error'] = true;
                        $error['msg'] = "Fullname is required field.";
                    }
                    break;
                case 'email':
                    if (trim($value)=="") {
                        $error['error'] = true;
                        $error['msg'] = "Email is required field.";
                    } else {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $error['error'] = true;
                            $error['msg'] = "Please enter valid email.";
                        }
                    }
                    break;
                case 'type':
                    if (trim($value)=="") {
                        $error['error'] = true;
                        $error['msg'] = "Type is required field.";
                    }
                    break;
                case 'priority':
                    if (trim($value)=="") {
                        $error['error'] = true;
                        $error['msg'] = "Priority is required field.";
                    }
                    break;
                case 'to_group':
                    if (trim($value)=="") {
                        $error['error'] = true;
                        $error['msg'] = "Group is required field.";
                    }
                    break;
                case 'status':
                    if (trim($value)=="") {
                        $error['error'] = true;
                        $error['msg'] = "Status is required field.";
                    }
                    break;
                case 'subject':
                    if (trim($value)=="") {
                        $error['error'] = true;
                        $error['msg'] = "Subject is required field.";
                    }
                    break;
                case 'query':
                    if (trim($value)=="") {
                        $error['error'] = true;
                        $error['msg'] = "Query is required field.";
                    }
                    break;
            }
        }
        return $error;
    }

    public function checkCustomer($wholedata, $websiteId)
    {
        if ($session = $this->_sessionManager->getTsCustomer()) {
            $tsCustomerId = $session['customer_id'];
            $ticketSystemCustomer = $this->_helpdeskCustomerFactory->create()->load($tsCustomerId);
            $wholedata['email'] = $ticketSystemCustomer->getEmail();
            $wholedata['fullname'] = $ticketSystemCustomer->getName();
        } elseif ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession->getCustomer();
            $ticketSystemCustomer = $this->_helpdeskCustomerFactory->create()->getCollection()
                                    ->addFieldToFilter("email", ["eq"=>$customer->getEmail()]);
            if (count($ticketSystemCustomer)) {
                foreach ($ticketSystemCustomer as $value) {
                    $value->setCustomerId($customer->getId());
                    $value->save();
                    $tsCustomerId = $value->getId();
                    $wholedata['email'] = $value->getEmail();
                    $wholedata['fullname'] = $value->getName();
                }
            } else {
                $ticketSystemCustomerModel = $this->_helpdeskCustomerFactory->create();
                $ticketSystemCustomerModel->setName($customer->getName());
                $ticketSystemCustomerModel->setEmail($customer->getEmail());
                $ticketSystemCustomerModel->setCustomerId($customer->getId());
                $saved = $ticketSystemCustomerModel->save();
                $tsCustomerId = $saved->getId();
                $wholedata['email'] = $saved->getEmail();
                $wholedata['fullname'] = $saved->getName();
            }
        } else {
            $ticketSystemCustomer = $this->_helpdeskCustomerFactory->create()->getCollection()
                                    ->addFieldToFilter("email", ["eq"=>$wholedata['email']]);
            if (count($ticketSystemCustomer)) {
                $customer = $this->_customerFactory->create()->setWebsiteId($websiteId);
                $customer->loadByEmail($wholedata['email']);
                foreach ($ticketSystemCustomer as $value) {
                    if ($customer->getId()) {
                        $value->setCustomerId($customer->getId());
                        $value->save();
                    }
                    $tsCustomerId = $value->getId();
                }
            } else {
                $customer = $this->_customerFactory->create()->setWebsiteId($websiteId);
                $customer->loadByEmail($wholedata['email']);
                $ticketSystemCustomerModel = $this->_helpdeskCustomerFactory->create();
                if ($customer->getId()) {
                    $ticketSystemCustomerModel->setCustomerId($customer->getId());
                }
                $ticketSystemCustomerModel->setName($wholedata['fullname']);
                $ticketSystemCustomerModel->setEmail($wholedata['email']);
                $saved = $ticketSystemCustomerModel->save();
                $tsCustomerId = $saved->getId();
            }
        }
        return $wholedata;
    }
}
