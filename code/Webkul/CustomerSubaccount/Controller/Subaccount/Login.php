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

class Login extends \Magento\Customer\Controller\AbstractAccount
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
     * Subaccount Model
     *
     * @var \Webkul\CustomerSubaccount\Model\SubaccountFactory
     */
    public $subaccountFactory;
    
    /**
     * Customer Model
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;

    /**
     * Logger
     *
     * @var \Webkul\CustomerSubaccount\Logger\Logger
     */
    public $logger;

    /**
     * AppState
     *
     * @var \Magento\Framework\App\State
     */
    public $appState;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\CustomerSubaccount\Logger\Logger $logger
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\CustomerSubaccount\Logger\Logger $logger,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->helper = $helper;
        $this->subaccountFactory = $subaccountFactory;
        $this->logger = $logger;
        $this->customerFactory = $customerFactory;
        $this->appState = $appState;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $customerId =  $this->helper->getCustomerId();
        if (!$this->helper->canLoginToSubAccounts($customerId) || !isset($data['id'])) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        $subaccount = $this->subaccountFactory->create()->load($data['id']);
        if (!$subaccount->getCustomerId()
            || !$this->helper->checkIfCustomerCanEditSubaccount($customerId, $subaccount->getCustomerId())) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        try {
            if ($subaccount->getStatus()) {
                $customer = $this->customerFactory->create()->load($subaccount->getCustomerId());
                $this->customerSession->setCustomerAsLoggedIn($customer);
                $this->messageManager->addSuccess(__('Logged in to sub-account.'));
                return $this->resultRedirectFactory->create()->setPath('customer/account/index', ['login'=>'subaccount']);
            } else {
                $this->messageManager->addError(
                    __('Sub-account is inactive.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong.'));
            $this->logger->info($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('wkcs/subaccount/index');
    }
}
