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

class BeforeSave implements ObserverInterface
{
    protected $coreRegistry;

    protected $helper;

    public function __construct(
        Registry $registry,
        \Webkul\CustomerApproval\Helper\Data $helper,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request
    ) {
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();
        if ($customerId) {
            $status = $this->helper->getCustomerApprovalStatus($customerId);
            $data = $this->request->getParams();
            //before save check for pending status
            if (isset($data['customer']['wk_customer_approval'])
                && $data['customer']['wk_customer_approval'] == '0'
                && $data['customer']['wk_customer_approval'] != $status
            ) {
                throw new \Magento\Framework\Validator\Exception(__('Customer Status can\'t be set to pending'));
            }
        }
    }
}
