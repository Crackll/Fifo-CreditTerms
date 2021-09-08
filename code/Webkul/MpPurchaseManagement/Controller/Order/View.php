<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Controller\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\MpPurchaseManagement\Model\OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpPurchaseManagement\Helper\Data
     */
    protected $helper;

    /**
     * @param Context                                             $context
     * @param PageFactory                                         $resultPageFactory
     * @param \Magento\Customer\Model\Session                     $customerSession
     * @param \Magento\Customer\Model\Url                         $customerUrl
     * @param \Webkul\MpPurchaseManagement\Model\OrderFactory     $orderFactory
     * @param \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory
     * @param \Webkul\Marketplace\Helper\Data                     $mpHelper
     * @param \Webkul\MpPurchaseManagement\Helper\Data            $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Webkul\MpPurchaseManagement\Model\OrderFactory $orderFactory,
        \Webkul\MpPurchaseManagement\Model\OrderItemFactory $orderItemFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpPurchaseManagement\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * view purchase order
     *
     * @return \Magento\Framework\Controller\Result\Redirect | \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->helper->getModuleStatus()) {
            $this->messageManager->addError(__('Module is disabled by admin, Please contact to admin!"'));
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account',
                ['_secure'=>$this->getRequest()->isSecure()]
            );
        }
        if ($this->mpHelper->isSeller() == 1) {
            $orderId = $this->getRequest()->getParam('id');
            if ($orderId) {
                $orderItemCollection = $this->orderItemFactory->create()->getCollection()
                                            ->addFieldToFilter('purchase_order_id', ['eq'=>$orderId])
                                            ->addFieldToFilter('seller_id', ['eq'=>$this->mpHelper->getCustomerId()]);
                if ($orderItemCollection->getSize()>0) {
                    /** @var \Magento\Framework\View\Result\Page $resultPage */
                    $resultPage = $this->resultPageFactory->create();
                    if ($this->mpHelper->getIsSeparatePanel()) {
                        $resultPage->addHandle('mppurchasemanagement_layout2_order_view');
                    }
                    $order = $this->orderFactory->create()->load($orderId);
                    $resultPage->getConfig()->getTitle()->set(__('Order %1', $order->getIncrementId()));
                    return $resultPage;
                } else {
                    $this->messageManager->addError(__('You are not authorized to access this order'));
                    return $this->resultRedirectFactory->create()->setPath(
                        'mppurchasemanagement/order/list',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $this->messageManager->addError(__('Some error occured, Please try again'));
                return $this->resultRedirectFactory->create()->setPath(
                    'mppurchasemanagement/order/list',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
