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

namespace Webkul\CustomerSubaccount\Controller\MyCarts;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Exception\NotFoundException;
use \Magento\Checkout\Model\Session as CheckoutSession;

class Place extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Context
     *
     * @var \Magento\Framework\App\Action\Context
     */
    public $context;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Subaccount Cart Model
     *
     * @var \Webkul\CustomerSubaccount\Model\CartFactory
     */
    public $subaccCartFactory;

    /**
     * Quote Model
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    public $quoteFactory;

    /**
     * Checkout Session
     *
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        CheckoutSession $checkoutSession
    ) {
        $this->helper = $helper;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $customerId = $this->helper->getCustomerId();
        if (!$this->helper->isSubaccountUser($customerId)
            || (!$this->helper->canMergeCartToMainCart($customerId)
                && !$this->helper->isCartApprovalRequired($customerId))) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        $data = $this->getRequest()->getParams();
        if (isset($data['id'])) {
            $subacc = $this->subaccCartFactory->create()->load($data['id'], 'quote_id');
            if ($subacc && $subacc->getCustomerId()==$customerId) {
                $quote = $this->quoteFactory->create()->load($data['id']);
                $quote->setIsActive(1)->save();
                $this->checkoutSession->replaceQuote($quote);
                return $this->resultRedirectFactory->create()->setPath('checkout');
            } else {
                $this->messageManager->addError(__('You are not authorised to place this cart.'));
            }
        }
        return $this->resultRedirectFactory->create()->setPath('wkcs/myCarts/index');
    }
}
