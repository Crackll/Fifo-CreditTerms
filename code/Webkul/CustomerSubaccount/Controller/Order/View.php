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

namespace Webkul\CustomerSubaccount\Controller\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Exception\NotFoundException;

class View extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

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
     * Order Model
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    public $orderFactory;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Construct
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $customerId = $this->helper->getCustomerId();
        $data = $this->getRequest()->getParams();
        if (isset($data['order_id'])) {
            $order = $this->orderFactory->create()->load($data['order_id']);
            if (($this->helper->canViewMainAccountOrderDetails($customerId)
                    && $this->helper->getMainAccountId($customerId) == $order->getCustomerId())
                ||
                ($this->helper->canViewSubAccountOrderDetails($customerId)
                    && $this->helper->checkIfCustomerCanEditSubaccount($customerId, $order->getCustomerId())
                )
            ) {
                $this->registry->register('current_order', $order);
                $resultPage = $this->_resultPageFactory->create();
                $layout = $resultPage->getLayout();
                return $resultPage;
            }
        }
        throw new NotFoundException(__('Action Not Allowed.'));
    }
}
