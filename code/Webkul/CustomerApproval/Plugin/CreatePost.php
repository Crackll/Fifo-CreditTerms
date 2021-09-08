<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerApproval
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerApproval\Plugin;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\UrlFactory;

class CreatePost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    protected $messagemanager;

    protected $resultRedirectFactory;

    protected $request;

    /**
     * @param CustomerRepositoryInterface                          $customerRepository
     * @param UrlFactory                                           $urlFactory
     * @param \Webkul\CustomerApproval\Helper\Data                 $helper
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Message\Manager                   $messagemanager
     * @param \Magento\Framework\App\RequestInterface              $request
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        UrlFactory $urlFactory,
        \Webkul\CustomerApproval\Helper\Data $helper,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\Manager $messagemanager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $http
    ) {
        $this->customerRepository = $customerRepository;
        $this->urlModel = $urlFactory->create();
        $this->customerApprovalHelper = $helper;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messagemanager = $messagemanager;
        $this->request = $request;
        $this->registry = $registry;
        $this->http = $http;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Account\CreatePost $subject,
        $result
    ) {
        if ($this->registry->registry('customer_register')) {
            $customer = $this->customerRepository->get($this->request->getParam('email'));
            $customerId = $customer->getId();

            if ($this->customerApprovalHelper->isAutoApproval()) { // approve
                $customer->setCustomAttribute("wk_customer_approval", '1');
                $this->customerRepository->save($customer);
                return $result;
            } else { //pending
                $customer->setCustomAttribute("wk_customer_approval", '0');
                $this->customerRepository->save($customer);
            }
            
            if ($customerId) {
                $this->customerApprovalHelper->afterRegisterMail($customer);
                $this->customerApprovalHelper->afterRegisterMailToAdmin($customer);
                $url = $this->urlModel->getUrl('customerapproval/account/index', ['_secure' => true]);
                $this->messagemanager->getMessages(true);
                $this->messagemanager->addSuccess(__($this->customerApprovalHelper->afterRegistrationMessage()));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($url);

                if ($this->http->getFullActionName() != 'b2bmarketplace_supplier_register') {
                    return $resultRedirect;
                }
            }
        }
        return $result;
    }
}
