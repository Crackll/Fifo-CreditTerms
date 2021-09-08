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

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Product Save Controller.
 */
class Submit extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @param Context          $context
     * @param Session          $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param SaveProduct      $saveProduct
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Webkul\Helpdesk\Helper\Tickets $ticketsHelper,
        \Webkul\Helpdesk\Helper\Data $helper,
        \Webkul\Helpdesk\Model\TicketsRepository $ticketsRepo,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\CustomerFactory $customerFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo,
        \Webkul\Helpdesk\Model\ThreadRepository $threadRepo,
        \Webkul\Helpdesk\Model\AttachmentRepository $attachmentRepo,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Magento\Customer\Model\CustomerFactory $customerDataFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_ticketsHelper = $ticketsHelper;
        $this->_helper = $helper;
        $this->_ticketsRepo = $ticketsRepo;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_customerFactory = $customerFactory;
        $this->_activityRepo = $activityRepo;
        $this->_threadRepo = $threadRepo;
        $this->_attachmentRepo = $attachmentRepo;
        $this->_threadFactory = $threadFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->customerDataFactory = $customerDataFactory;
        parent::__construct(
            $context
        );
    }

      /**
       * @inheritDoc
       */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * seller product save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $customer_id = $this->_customerSession->getCustomerId();
            $customerData = $this->customerDataFactory->create()->load($customer_id);
            $userId = $this->_ticketsHelper->getTsCustomerId();
            $isloginRequired = $this->_helper->getLoginRequired();
            if ($this->getRequest()->isPost()) {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    $this->messageManager->addError(__("Form key is not valid!!"));
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/newticket',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                if (!$userId && $isloginRequired) {
                    $this->messageManager->addError(__("Please Login !!"));
                    $this->_redirect("helpdesk/ticket/login/");
                } else {
                    $adminEmail = $this->_helper->getConfigHelpdeskEmail();
                    $wholedata = $this->getRequest()->getParams();
                    $wholedata['source'] = "website";
                    $wholedata['who_is'] = "customer";
                    $wholedata['from'] = $wholedata['email'] ?? $customerData['email'];
                    $wholedata['to'] = $adminEmail;
                    $ticketId = $this->_ticketsRepo->createTicket($wholedata);
                    $senderInfo = ['name'=>$wholedata['fullname'] ?? $customerData
                    ['firstname']." ".$customerData['lastname'],
                    'email'=>$wholedata['email'] ?? $customerData['email'] ];
                    $receiverInfo = ['name'=>'Admin','email'=>$adminEmail];
                    $emailTempVariables['name'] = $receiverInfo['name'];
                    $emailTempVariables['ticket_id'] = $ticketId;
                    $emailTempVariables['customer_name'] = $wholedata['fullname'] ?? $customerData['name'];
                    $emailTempVariables['customer_email'] = $wholedata['email'] ?? $customerData['email'];
                    $template_name = "helpdesk/email/helpdesk_mail";
                    $this->_helper->sendMail(
                        $template_name,
                        $emailTempVariables,
                        $senderInfo,
                        $receiverInfo
                    );
                    $ticket = $this->_ticketsFactory->create()->load($ticketId);
                    $customer = $this->_customerFactory->create()->load($ticket->getCustomerId());
                    $this->_activityRepo->saveActivity($ticketId, $customer->getName(), "add", "ticket");
                    $wholedata['thread_type'] = "create";
                    $threadId = $this->_threadRepo->createThread($ticketId, $wholedata);
                    $files = $this->getRequest()->getFiles();
                    if (isset($files["fupld"]["tmp_name"][0])) {
                        $this->_attachmentRepo->saveAttachment($ticketId, $threadId);
                    } else {
                        $this->_threadFactory->create()->load($threadId)->setAttachment(0)
                        ->save();
                    }
                    $this->messageManager->addSuccess(__("Your ticket has been created successfully")."<br>".__("Your ticket id is : #").$ticketId);
                }
            } else {
                $this->messageManager->addError(__("Sorry Nothing Found To Save!!"));
            }
            if ($userId) {
                return $this->_redirect("helpdesk/ticket/mytickets/");
            } else {
                return $this->_redirect("helpdesk/ticket/login/");
            }
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_helpdeskLogger->info($e->getMessage());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_helpdeskLogger->info($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->_helpdeskLogger->info($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/newticket',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
