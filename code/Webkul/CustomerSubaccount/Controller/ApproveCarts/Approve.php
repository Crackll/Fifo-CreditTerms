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
namespace Webkul\CustomerSubaccount\Controller\ApproveCarts;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Exception\NotFoundException;

class Approve extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Context
     *
     * @var \Magento\Framework\App\Action\Context
     */
    public $context;

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
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Email Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Email
     */
    public $emailHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Webkul\CustomerSubaccount\Helper\Email $emailHelper
     */
    public function __construct(
        Context $context,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Webkul\CustomerSubaccount\Helper\Email $emailHelper
    ) {
        $this->helper = $helper;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->quoteFactory = $quoteFactory;
        $this->emailHelper = $emailHelper;
        parent::__construct($context);
    }
    
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if (!$this->helper->canApproveCarts()) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        $data = $this->getRequest()->getParams();
        if (isset($data['id'])) {
            $subaccCart = $this->subaccCartFactory->create()->load($data['id'], 'quote_id')->setStatus(1)->save();
            $this->emailHelper->sendCartApprovedNotification($subaccCart);
            $this->messageManager->addSuccess(__('Cart approved successfully.'));
        }
        return $this->resultRedirectFactory->create()->setPath('wkcs/approveCarts/index');
    }
}
