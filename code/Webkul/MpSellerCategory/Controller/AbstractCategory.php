<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\MpSellerCategory\Helper\Data as HelperData;

abstract class AbstractCategory extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var SubAccount
     */
    protected $_subAccount;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var SubAccountRepositoryInterface
     */
    protected $_subAccountRepository;

    /**
     * @var MarketplaceHelper
     */
    protected $_marketplaceHelper;

    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Webkul\MpSellerCategory\Model\CategoryFactory
     */
    protected $_mpSellerCategoryFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param MarketplaceHelper $marketplaceHelper
     * @param HelperData $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $customerSession,
        FormKeyValidator $formKeyValidator,
        MarketplaceHelper $marketplaceHelper,
        HelperData $helper,
        \Magento\Framework\Registry $registry,
        \Webkul\MpSellerCategory\Model\CategoryFactory $mpSellerCategoryFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_url = $url;
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->_mpSellerCategoryFactory = $mpSellerCategoryFactory;
        parent::__construct($context);
    }

    /**
     * Check authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_helper->isAllowedSellerCategories()) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/dashboard/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        if (!$this->_helper->isAllowedSellerToManageCategories()) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/dashboard/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Get Seller Category
     *
     * @return \Webkul\MpSellerCategory\Model\Category
     */
    public function getSellerCategory()
    {
        $sellerCategory = $this->_mpSellerCategoryFactory->create();
        if (!empty($this->getRequest()->getParam("id"))) {
            $sellerCategory->load($this->getRequest()->getParam("id"));
        }

        return $sellerCategory;
    }

    /**
     * Get Current Seller Id
     *
     * @return integer
     */
    public function getSellerId()
    {
        return (int) $this->_marketplaceHelper->getCustomerId();
    }
}
