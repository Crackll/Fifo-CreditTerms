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

class Authenticate implements ObserverInterface
{
    protected $coreRegistry;

    protected $helper;

    public function __construct(
        Registry $registry,
        \Webkul\CustomerApproval\Helper\Data $helper
    ) {
        $this->coreRegistry = $registry;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getModel();
        $customerId = $customer->getId();
        $status = $this->helper->getCustomerApprovalStatus($customerId);
        if ($status == Options::PENDING) { // pending
            throw new \Magento\Framework\Exception\LocalizedException(__($this->helper->afterLoginMessage()));
        } elseif ($status == Options::REJECTED) { //rejected
            throw new \Magento\Framework\Exception\LocalizedException(__($this->helper->afterLoginMessage()));
        }
    }
}
