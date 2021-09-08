<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c)   Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

abstract class AbstractAds extends Action
{

    const CURRENT_BLOCK = 'current_block';

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\MpAdvertisementManager\Api\BlockRepositoryInterface
     */
    protected $_blockRepository;

    /**
     * @var \Magento\MpAdvertisementManager\Api\Data\BlockInterfaceFactory
     */
    protected $_blockDataFactory;

    /**
     * @var \Magento\MpAdvertisementManager\Api\PricingRepositoryInterface
     */
    protected $_pricingRepository;

    /**
     * @var \Magento\MpAdvertisementManager\Api\Data\PricingInterfaceFactory
     */
    protected $_pricingDataFactory;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $_cartManager;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $_cartItemManager;

    /**
     * @var \Magento\Quote\Api\Data\CartItemInterface
     */
    protected $_cartDataItem;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productOption;

     /**
      * @var Magento\Quote\Api\Data\ProductOptionExtensionInterface
      */
    protected $_productOptionExtension;

    /**
     * $_helper
     *
     * @var Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_helper ;

    /**
     * $_orderHelper
     *
     * @var Webkul\MpAdvertisementManager\Helper\Order
     */
    protected $_orderHelper ;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_serializer;

    /**
     * Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_adapterFactory;

    protected $date;

    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession   customer session
     */
    public function __construct(
        Context $context,
        \Webkul\MpAdvertisementManager\Api\BlockRepositoryInterface $blockRepository,
        \Webkul\MpAdvertisementManager\Api\Data\BlockInterfaceFactory $blockDataFactory,
        \Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface $pricingRepository,
        \Webkul\MpAdvertisementManager\Api\Data\PricingInterfaceFactory $pricingDataFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManager,
        \Magento\Quote\Api\CartItemRepositoryInterface $cartItemManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Quote\Api\Data\CartItemInterface $cartDataItem,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Api\Data\ProductOptionInterface $productOption,
        \Magento\Quote\Api\Data\ProductOptionExtensionInterface $productOptionExtension,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        Session $customerSession,
        \Webkul\MpAdvertisementManager\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Customer\Model\Url $url,
        \Webkul\MpAdvertisementManager\Helper\Order $orderHelper,
        UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\Session $sessionBackend,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {

        $this->_blockRepository = $blockRepository;
        $this->_blockDataFactory = $blockDataFactory;
        $this->_pricingRepository = $pricingRepository;
        $this->_pricingDataFactory = $pricingDataFactory;
        $this->_cartManager = $cartManager;
        $this->_cartItemManager = $cartItemManager;
        $this->_cartDataItem = $cartDataItem;
        $this->fileSystem = $fileSystem;
        $this->productRepository = $productRepository;
        $this->_productFactory = $productFactory;
        $this->_productOption = $productOption;
        $this->_productOptionExtension = $productOptionExtension;
        $this->cartModel = $cartModel;
        $this->_customerSession = $customerSession;
        $this->sessionBackend = $sessionBackend;
        $this->_quoteFactory = $quoteFactory;
        $this->url = $url;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_helper = $helper;
        $this->_orderHelper = $orderHelper;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_mpHelper = $mpHelper;
        $this->_serializer = $serializer;
        $this->_scopeConfig = $scopeConfig;
        $this->_escaper = $escaper;
        $this->_adapterFactory = $adapterFactory;
        $this->date= $date;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
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
        $loginUrl = $this->url->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        if (!$this->_helper->getMouduleStatus()) {
            $this
                ->resultFactory
                ->create('forward')
                ->forward('noroute');
        }

        return parent::dispatch($request);
    }

    /**
     * getJsonResponse returns json response.
     *
     * @param array $responseContent
     *
     * @return JSON
     */
    protected function getJsonResponse($responseContent = [])
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
