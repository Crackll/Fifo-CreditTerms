<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPriceList\Controller\SellerPriceList;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul MpPriceList PriceList Edit Controller.
 */
class Edit extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\MpPriceList\Model\PriceListFactory
     */
    protected $priceList;

    /**
     * @var \Webkul\MpPriceList\Helper\Data
     */
    protected $pricelistHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\UrlFactory
     */
    protected $modelUrl;

   /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
    * @param \Magento\Customer\Model\Session $customerSession
    * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
    * @param \Webkul\MpPriceList\Model\PriceListFactory $priceList
    * @param \Webkul\MpPriceList\Helper\Data $pricelistHelper
    * @param \Magento\Customer\Model\UrlFactory $modelUrl
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpPriceList\Model\PriceListFactory $priceList,
        \Webkul\MpPriceList\Helper\Data $pricelistHelper,
        \Magento\Customer\Model\UrlFactory $modelUrl
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct(
            $context
        );
        $this->_resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->priceList = $priceList;
        $this->pricelistHelper = $pricelistHelper;
        $this->modelUrl =  $modelUrl;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl =  $this->modelUrl->create()
        ->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Seller Product Edit Action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->marketplaceHelper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $priceListId = (int) $this->getRequest()->getParam('id');
            $rightSeller = $this->pricelistHelper->isRightSeller($priceListId);
            if ($rightSeller == 1) {
                $priceListData = $this->priceList->create()->load($priceListId, 'id');

                if ($priceListId && !$priceListData->getId()) {
                    $this->messageManager->addError(
                        __('This pricelist no longer exists.')
                    );
                    $resultRedirect = $this->resultRedirectFactory->create();

                    return $resultRedirect->setPath(
                        '*/*/managepricelist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                if ($priceListId) {
                    /** @var \Magento\Framework\View\Result\Page $resultPage */
                    $resultPage = $this->_resultPageFactory->create();
                    if ($helper->getIsSeparatePanel()) {
                        $resultPage->addHandle('mppricelist_sellerpricelist_edit_layout2');
                    }
                    $resultPage->getConfig()->getTitle()->set(
                        __('Edit Price List')
                    );
                    return $resultPage;
                } else {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/addpricelist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'mppricelist/sellerpricelist/managepricelist',
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
