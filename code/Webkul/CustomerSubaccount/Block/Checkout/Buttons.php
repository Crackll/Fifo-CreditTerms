<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Block\Checkout;

class Buttons extends \Magento\Framework\View\Element\Template
{
    /**
     * Context
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    public $context;

    /**
     * Subaccount Cart
     *
     * @var \Webkul\CustomerSubaccount\Model\CartFactory
     */
    public $subaccCartFactory;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->subaccCartFactory = $subaccCartFactory;
        parent::__construct($context, $data);
    }

    /**
     * Is Cart Approval Required?
     *
     * @return boolean
     */
    public function isCartApprovalRequired()
    {
        $customerId = $this->helper->getCustomerId();
        $result = $this->helper->canPlaceOrder($customerId)
                    && $this->helper->isCartApprovalRequired($customerId)
                    && !$this->helper->isCartApproved();
        return $result;
    }

    /**
     * Can Merge To Main Cart?
     *
     * @return boolean
     */
    public function canMergeCartToMainCart()
    {
        $customerId = $this->helper->getCustomerId();
        $result = $this->helper->isSubaccountUser($customerId)
                    && $this->helper->canMergeCartToMainCart($customerId);
        return $result;
    }
}
