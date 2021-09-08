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
class CheckoutCartProductAddAfter implements ObserverInterface
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
     * Constructor
     *
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     */
    public function __construct(
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
    ) {
        $this->helper = $helper;
        $this->subaccCartFactory = $subaccCartFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteId = $this->helper->getQuote()->getId();
        if ($quoteId) {
            $subaccCartModel = $this->subaccCartFactory->create()->load($quoteId, 'quote_id');
            if ($subaccCartModel->getId()) {
                $subaccCartModel->setStatus(0)->save();
            }
        }
    }
}
