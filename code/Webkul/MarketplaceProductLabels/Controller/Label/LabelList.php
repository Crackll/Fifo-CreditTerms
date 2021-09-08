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
use Webkul\MarketplaceProductLabels\Helper\Data as Helper;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Customer\Model\UrlFactory as UrlFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

/**
 * Webkul Marketplace Productlist controller.
 */
class LabelList extends Action
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
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var Webkul\MarketplaceProductLabels\Helper\Data
     */
    protected $helper;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Helper $Helper
     * @param MpHelper $MpHelper
     * @param UrlFactory $urlFactory
     * @param CustomerSessionFactory $customerSessionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Helper $helper,
        MpHelper $mpHelper,
        UrlFactory $urlFactory,
        CustomerSessionFactory $customerSessionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
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
        $isPartner = $this->mpHelper->isSeller();

        if ($isPartner == 1 && $this->helper->getConfigData('label_manage')) {
            $resultPage = $this->resultPageFactory->create();
            if ($this->mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mplabels_layout2_label_labellist');
                $resultPage->getConfig()->getTitle()->set(
                    __('Product Label List')
                );
                return $resultPage;
            } else {
                $resultPage->getConfig()->getTitle()->set(
                    __('Product Label List')
                );
                return $resultPage;
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
