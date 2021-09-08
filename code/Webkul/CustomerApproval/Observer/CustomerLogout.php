<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_CustomerApproval
 * @author    Webkul <support@webkul.com>
 * @license   https://store.webkul.com/license.html ASL Licence
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\CustomerApproval\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;

class CustomerLogout implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Magento\Customer\Model\Session
     */
    private $session;
    
    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     */
    protected $customerRepositoryInterface;

   /**
    * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    * @param Session $customerSession
    */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\App\Response\RedirectInterface $responseFactory
    ) {
        $this->session = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->urlModel = $urlFactory->create();
        $this->responseFactory = $responseFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getControllerAction();
        if ($this->session->isLoggedIn()) {
            $customerId = $this->session->getCustomerId();
            $customer = $this->customerRepositoryInterface->getById($customerId);
            $attributeValue = $customer->getCustomAttribute('wk_customer_approval')->getValue();

            if ($attributeValue == 2) {
                $this->session->logout()->setLastCustomerId($customerId);
                if (!$this->session->isLoggedIn()) {
                    $result = $this->responseFactory->redirect($controller->getResponse(), 'customer/account/login');
                    return false;
                }
            }
        }
    }
}
