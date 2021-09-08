<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Message\ManagerInterface;

class CustomerLoginAfter implements ObserverInterface
{
    /**
     * @param SessionManager  $coreSession
     */
    public function __construct(
        SessionManager $coreSession,
        \Magento\Customer\Model\SessionFactory $session,
        \Webkul\Helpdesk\Model\CustomerFactory $customerFactory,
        \Webkul\Helpdesk\Model\TicketsFactory $tickets
    ) {
        $this->_coreSession = $coreSession;
        $this->session = $session;
        $this->customerFactory = $customerFactory;
        $this->tickets = $tickets;
    }

    /**
     * customer Login event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_coreSession->unsTsCustomer();
        $email = $this->session->create()->getCustomerData()->getEmail();
        $id    = $this->session->create()->getCustomerData()->getId();
        $customer = $this->customerFactory->create()->getCollection();
        $customer->addFieldToFilter('email', $email);
        if ($customer->getSize()) {
            foreach ($customer as $cust) {
                $cust->setCustomerId($id);
                $cust->save();
                $ticket = $this->tickets->create()->getCollection();
                $ticket->addFieldToFilter('email', $email);
                foreach ($ticket as $tic) {
                    $tic->setCustomerId($cust->getId());
                    $tic->save();
                }
            }
            
        }
    }
}
