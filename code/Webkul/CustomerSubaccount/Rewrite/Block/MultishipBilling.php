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
namespace Webkul\CustomerSubaccount\Rewrite\Block;

class MultishipBilling extends \Magento\Multishipping\Block\Checkout\Billing
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification
     * @param array $data
     * @param array $additionalChecks
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        array $data = [],
        array $additionalChecks = []
    ) {
        $this->_multishipping = $multishipping;
        $this->_checkoutSession = $checkoutSession;
        $this->paymentSpecification = $paymentSpecification;
        $this->helper = $helper;
        $this->accountManagement = $accountManagement;
        $this->coreSession = $coreSession;
        parent::__construct(
            $context,
            $paymentHelper,
            $methodSpecificationFactory,
            $multishipping,
            $checkoutSession,
            $paymentSpecification,
            $data,
            $additionalChecks
        );
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve billing address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        $address = $this->getData('address');
        if ($address === null) {
            if ($this->helper->isForcedMainAddress() && $this->coreSession->getMultishipForced()) {
                $mainAccId = $this->helper->getMainAccountId();
                $defaddress = $this->accountManagement->getDefaultBillingAddress($mainAccId);
                $this->_multishipping->setQuoteCustomerBillingAddress(
                    $defaddress->getId()
                );
            }
            $address = $this->_multishipping->getQuote()->getBillingAddress();
            $this->setData('address', $address);
        }
        $this->coreSession->unsMultishipForced();
        return $address;
    }
}
