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

use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\ResultFactory;
use Webkul\CustomerApproval\Model\Options;

class Confirm
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

    /**
     * @var Session
     */
    protected $session;

    protected $messageManager;

    /**
     * @param Session                                     $customerSession
     * @param AccountManagementInterface                  $customerAccountManagement
     * @param CustomerRepositoryInterface                 $customerRepository
     * @param UrlFactory                                  $urlFactory
     * @param ResultFactory                               $resultFactory
     * @param \Magento\Framework\App\RequestInterface     $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\CustomerApproval\Helper\Data        $helper
     */
    public function __construct(
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        UrlFactory $urlFactory,
        ResultFactory $resultFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\CustomerApproval\Helper\Data $helper
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->urlModel = $urlFactory->create();
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->customerApprovalHelper = $helper;
    }

    public function aroundExecute(
        \Magento\Customer\Controller\Account\Confirm $subject,
        \Closure $proceed
    ) {
        $reflFoo = new \ReflectionClass(\Magento\Customer\Controller\Account\Confirm::class);
        $getCookieManager = $reflFoo->getMethod('getCookieManager');
        $getCookieManager->setAccessible(true);
        $getCookieMetadataFactory = $reflFoo->getMethod('getCookieMetadataFactory');
        $getCookieMetadataFactory->setAccessible(true);
        $getSuccessMessage = $reflFoo->getMethod('getSuccessMessage');
        $getSuccessMessage->setAccessible(true);
        $getSuccessRedirect = $reflFoo->getMethod('getSuccessRedirect');
        $getSuccessRedirect->setAccessible(true);
        
        /**
 * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
*/
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->session->isLoggedIn()) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        try {
            $customerId = $this->request->getParam('id', false);
            $key = $this->request->getParam('key', false);
            if (empty($customerId) || empty($key)) {
                throw new \AuthenticationException(__('Bad request.'));
            }

            // send greeting email
            $customerEmail = $this->customerRepository->getById($customerId)->getEmail();
            $customer = $this->customerAccountManagement->activate($customerEmail, $key);
            $status = $this->customerApprovalHelper->getCustomerApprovalStatus($customerId);
            if ($status == Options::Pending || $status == Options::Rejected) { // check customer approval
                $this->messageManager->addError(__($this->customerApprovalHelper->afterRegistrationMessage()));
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            } else {    //set login
                $this->session->setCustomerDataAsLoggedIn($customer);
                if ($getCookieManager->invoke($subject, null)->getCookie('mage-cache-sessid')) {
                    $metadata = $getCookieMetadataFactory->invoke($subject, null)->createCookieMetadata();
                    $metadata->setPath('/');
                    $getCookieManager->invoke($subject, null)->deleteCookie('mage-cache-sessid', $metadata);
                }
            }
            
            $this->messageManager->addSuccess($getSuccessMessage->invoke($subject, null));
            $resultRedirect->setUrl($getSuccessRedirect->invoke($subject, null));
            return $resultRedirect;
        } catch (StateException $e) {
            $this->messageManager->addException($e, __('This confirmation key is invalid or has expired.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was an error confirming the account'));
        }

        $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        return $resultRedirect->setUrl($this->_redirect->error($url));
    }
}
