<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Wholesaler;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Url;
use Webkul\MpWholesale\Model\Storage\LeadStorage;

class View extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelperData;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData
     * @param \Webkul\MpWholesale\Helper\Data $mpWholesaleHelper
     * @param LeadStorage $leadStorage
     * @param Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        \Webkul\MpWholesale\Helper\Data $mpWholesaleHelper,
        LeadStorage $leadStorage,
        Url $customerUrl
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelperData = $marketplaceHelperData;
        $this->mpWholesaleHelper = $mpWholesaleHelper;
        $this->leadStorage = $leadStorage;
        $this->customerUrl = $customerUrl;
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
     * Wholesaler Wholesaler View page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->mpWholesaleHelper->getModuleStatus()) {
            $isPartner = $this->marketplaceHelperData->isSeller();
            $productId = $this->getRequest()->getParam('id');
            $status = $this->mpWholesaleHelper->isWholeSalerExistForProduct($productId);
            if ($status) {
                if ($isPartner == 1) {
                    $resultPage = $this->resultPageFactory->create();
                    if ($this->marketplaceHelperData->getIsSeparatePanel()) {
                        $resultPage->addHandle('mpwholesale_layout2_wholesaler_view');
                    }
                    $product = $this->mpWholesaleHelper->getProduct($productId);
                    $resultPage->getConfig()->getTitle()->set(__('Wholesaler List of %1', $product->getName()));
                    $this->leadStorage->saveLeadData($productId);
                    return $resultPage;
                } else {
                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/account/becomeseller',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $this->messageManager->addError("Requested product not found !!!!");
                return $this->resultRedirectFactory->create()->setPath(
                    'mpwholesale/product/view',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            $this->messageManager->addError(__("Module is disabled by admin, Please contact to admin!"));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'customer/account',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
    }
}
