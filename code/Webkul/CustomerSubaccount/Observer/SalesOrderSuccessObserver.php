<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_CustomerSubaccount
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer class.
 */
class SalesOrderSuccessObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Subaccount Cart
     *
     * @var \Webkul\CustomerSubaccount\Model\CartFactory
     */
    public $subaccCartFactory;

    /**
     * Email Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Email
     */
    public $emailHelper;

    /**
     * Constructor
     *
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Webkul\CustomerSubaccount\Helper\Email $emailHelper
     */
    public function __construct(
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Webkul\CustomerSubaccount\Helper\Email $emailHelper
    ) {
        $this->helper = $helper;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->emailHelper = $emailHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $quoteId = $order->getQuoteId();
        $subaccCartModel = $this->subaccCartFactory->create()->load($quoteId, 'quote_id');
        if ($subaccCartModel && $subaccCartModel->getId()) {
            $subaccCartModel->setStatus(2)->save();
        }
    }
}
