<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Controller\Subaccount;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Exception\NotFoundException;

class Save extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Context
     *
     * @var \Magento\Framework\App\Action\Context
     */
    public $context;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Email Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Email
     */
    public $emailHelper;

    /**
     * Subaccount Model
     *
     * @var \Webkul\CustomerSubaccount\Model\SubaccountFactory
     */
    public $subaccountFactory;

    /**
     * Logger
     *
     * @var \Webkul\CustomerSubaccount\Logger\Logger
     */
    public $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory
     * @param \Webkul\CustomerSubaccount\Logger\Logger $logger
     * @param \Webkul\CustomerSubaccount\Helper\Email $emailHelper
     */
    public function __construct(
        Context $context,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        \Webkul\CustomerSubaccount\Logger\Logger $logger,
        \Webkul\CustomerSubaccount\Helper\Email $emailHelper
    ) {
        $this->helper = $helper;
        $this->subaccountFactory = $subaccountFactory;
        $this->logger = $logger;
        $this->emailHelper = $emailHelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        if (!$this->helper->canCreateSubAccounts() && !isset($data['entity_id'])) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        $customerId =  $this->helper->getCustomerId();
        if (isset($data['entity_id'])) {
            if (!($this->helper->canEditSubAccounts()
                && $this->helper->checkIfCustomerCanEditSubaccount($customerId, $data['customer_id']))) {
                throw new NotFoundException(__('Action Not Allowed.'));
            }
        }
        try {
            $data = $this->getRequest()->getParams();
            $data['available_permissions'] = implode(',', $data['available_permissions']);
            if (!isset($data['status'])) {
                $data['status'] = 0;
            }
            if (isset($data['entity_id'])) {
                $subaccount = $this->subaccountFactory->create()->load($data['entity_id']);
                $response = $this->helper->saveCustomerData($data, $subaccount->getCustomerId());
            } else {
                $subaccount = $this->subaccountFactory->create();
                $response = $this->helper->saveCustomerData($data);
                $data['parent_account_id'] = $customerId;
                $data['main_account_id'] = $this->helper->getMainAccountId($customerId);
                if ($this->helper->getSubaccountApprovalRequired()) {
                    $data['admin_approved'] = 0;
                }
            }
            if (!$response['error']) {
                $data['customer_id'] = $response['customer_id'];
                $subaccount->setData($data)->save();
                if (!isset($data['entity_id'])) {
                    $this->emailHelper->sendSubAccountCreatedNotificationToAdmin($subaccount);
                    if ($this->helper->isSubaccountUser($customerId)) {
                        $this->emailHelper->sendSubAccountCreatedNotificationToMainAccount($subaccount);
                    }
                    $this->messageManager->addSuccess(__('Sub Account created successfully.'));
                } else {
                    $this->messageManager->addSuccess(__('Sub Account details saved successfully.'));
                }
            } else {
                $this->messageManager->addError($response['message']);
                $this->logger->info($response['message']);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong.'));
            $this->logger->info($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('wkcs/subaccount/index');
    }
}
