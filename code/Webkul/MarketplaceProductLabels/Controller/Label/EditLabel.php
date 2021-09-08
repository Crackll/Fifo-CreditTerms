<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Controller\Label;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Helper\Data as MpHelperData;
use Webkul\MarketplaceProductLabels\Helper\Data as LabelHelperData;
use Magento\Customer\Model\UrlFactory as UrlFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

/**
 * Webkul Marketplace Productlist controller.
 */
class EditLabel extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSessionFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var MpHelperData
     */
    protected $mpHelperData;

    /**
     * @var LabelHelperData
     */
    protected $labelHelperData;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param MpHelperData $mpHelperData
     * @param LabelHelperData $labelHelperData
     * @param UrlFactory $urlFactory
     * @param CustomerSessionFactory $customerSessionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MpHelperData $mpHelperData,
        LabelHelperData $labelHelperData,
        UrlFactory $urlFactory,
        CustomerSessionFactory $customerSessionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->mpHelperData = $mpHelperData;
        $this->labelHelperData = $labelHelperData;
        $this->urlFactory = $urlFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        parent::__construct($context);
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
        $loginUrl = $this->urlFactory->create()->getLoginUrl();

        if (!$this->customerSessionFactory->create()->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $labelId = $this->getRequest()->getParam('id');
        
        $rightseller = $this->labelHelperData->isRightSeller($labelId);
        $resultPage = $this->resultPageFactory->create();
        $isPartner = $this->mpHelperData->isSeller();

        if ($isPartner && $rightseller && $this->labelHelperData->getConfigData('label_manage')) {
            if ($this->mpHelperData->getIsSeparatePanel()) {
                $resultPage->addHandle('mplabels_layout2_label_editlabel');
                $resultPage->getConfig()->getTitle()->set(__('Edit Product Label'));
                return $resultPage;
            } else {
                $resultPage->getConfig()->getTitle()->set(__('Edit Product Label'));
                return $resultPage;
            }
        } else {
            $this->messageManager->addError(
                __('This label not associated with current seller.')
            );
            return $this->resultRedirectFactory->create()->setPath('*/label/labellist/');
        }
    }
}
