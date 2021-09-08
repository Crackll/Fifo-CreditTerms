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
namespace Webkul\CustomerSubaccount\Controller\MergeCarts;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Exception\NotFoundException;

class Delete extends \Magento\Customer\Controller\AbstractAccount
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
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Webkul\CustomerSubaccount\Model\CartFactory $subaccCartFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->subaccCartFactory = $subaccCartFactory;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
        if ($this->helper->isSubaccountUser()) {
            throw new NotFoundException(__('Action Not Allowed.'));
        }
        $data = $this->getRequest()->getParams();
        if (isset($data['id'])) {
            $this->subaccCartFactory->create()->load($data['id'], 'quote_id')->delete();
            $this->quoteFactory->create()->load($data['id'])->delete();
            $this->messageManager->addSuccess(__('Cart deleted successfully.'));
        }
        return $this->resultRedirectFactory->create()->setPath('wkcs/mergeCarts/index');
    }
}
