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

class ListAction extends Action
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
     * @param \Webkul\Marketplace\Helper\Data                     $mpHelper
     * @param \Webkul\MpPurchaseManagement\Helper\Data            $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpPurchaseManagement\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
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
     * list purchase orders
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
            $resultPage = $this->resultPageFactory->create();
            if ($this->mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mppurchasemanagement_layout2_order_list');
            }
            $resultPage->getConfig()->getTitle()->set(__('Purchase Orders'));
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
