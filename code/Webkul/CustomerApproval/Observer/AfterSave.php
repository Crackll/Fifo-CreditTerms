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

namespace Webkul\CustomerApproval\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Webkul\CustomerApproval\Model\Options;

class AfterSave implements ObserverInterface
{
    protected $coreRegistry;

    protected $helper;

    public function __construct(
        Registry $registry,
        \Webkul\CustomerApproval\Helper\Data $helper,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Magento\Customer\Model\EmailNotification $email
    ) {
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        $this->request = $request;
        $this->email = $email;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // commmented due to reported isue : gettig Welcme email everytime after customer edit
        // $customer = $observer->getCustomer();
        // $customerId = $customer->getId();
        // $status = $this->helper->getCustomerApprovalStatus($customerId);
        // $data = $this->request->getParams();

        // if (isset($data['customer']['wk_customer_approval'])
        //     && $data['customer']['wk_customer_approval'] != Options::PENDING
        // ) {
        //     if ($data['customer']['wk_customer_approval'] == Options::APPROVED) {
        //         $this->helper->sendApprovalMail($customer);
        //         $this->email->newAccount($customer);
        //     } else {
        //         $this->helper->sendDisapprovalMail($customer);
        //     }
        // }
    }
}
