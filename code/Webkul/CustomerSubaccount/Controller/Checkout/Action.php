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
namespace Webkul\CustomerSubaccount\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Exception\NotFoundException;
use \Magento\Checkout\Model\Session as CheckoutSession;

class Action extends \Magento\Customer\Controller\AbstractAccount
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
     * Logger
     *
     * @var \Webkul\CustomerSubaccount\Logger\Logger
     */
    public $logger;

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
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Webkul\CustomerSubaccount\Logger\Logger $logger
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Webkul\CustomerSubaccount\Logger\Logger $logger,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->helper = $helper;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if (!isset($data['type']) || !in_array($data['type'], [1,2])) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        $customerId =  $this->helper->getCustomerId();
        if ($data['type'] == 1
            &&
            !(
                $this->helper->canPlaceOrder($customerId)
                && $this->helper->isCartApprovalRequired($customerId)
                && !$this->helper->isCartApproved()
            )
        ) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        if ($data['type'] == 2
            &&
            !(
                $this->helper->isSubaccountUser($customerId)
                && $this->helper->canMergeCartToMainCart($customerId)
            )
        ) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        
        try {
            $data['customer_id'] = $customerId;
            $data['status'] = 0;
            $quote = $this->helper->getQuote();
            $data['quote_id'] = $quote->getId();
            $subaccCartModel = $this->subaccCartFactory->create()->load($data['quote_id'], 'quote_id');
            $id = $subaccCartModel->getId();
            $subaccCartModel->setData($data);
            $subaccCartModel->setId($id);
            $subaccCartModel->save();
            if ($data['type'] == 1) {
                $this->messageManager->addSuccess(__('Cart sent for Approval.'));
            } else {
                $this->messageManager->addSuccess(__('Cart sent for Merge.'));
            }
            $quote->setIsActive(0)->save();
            $newquote = $this->quoteFactory->create();
            $newquote->setIsActive(1)->save();
            $this->checkoutSession->replaceQuote($newquote);
            return $this->resultRedirectFactory->create()->setPath('wkcs/myCarts', ['cart'=>'update']);
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong.'));
            $this->logger->info($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }
}
