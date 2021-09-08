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

class Register implements ObserverInterface
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
        $customer = $observer->getCustomer();
        $this->coreRegistry->register('customer_register', 1);
    }
}
