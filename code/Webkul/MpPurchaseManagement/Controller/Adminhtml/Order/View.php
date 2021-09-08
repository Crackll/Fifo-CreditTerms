<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Webkul\MpPurchaseManagement\Controller\Adminhtml\Order
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param Context                                         $context
     * @param PageFactory                                     $resultPageFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * View action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $order = $this->orderFactory->create()->load($this->getRequest()->getParam('id'));
        if ($order->getEntityId()) {
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Webkul_MpWholesale::manager');
            $resultPage->getConfig()->getTitle()->prepend($order->getIncrementId());
            $resultPage->addLeft(
                $resultPage->getLayout()->createBlock(
                    \Webkul\MpPurchaseManagement\Block\Adminhtml\Order\Edit\Tabs::class
                )
            );
            return $resultPage;
        } else {
            $this->messageManager->addError(__('This order no longer exists'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
    }
}
