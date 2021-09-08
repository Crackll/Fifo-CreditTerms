<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWalletSystem\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;

/**
 * Webkul MpWalletSystem Model Class
 */
class PaymentMethod extends AbstractMethod
{
    const CODE = 'mpwalletsystem';

    /**
     * @var string
     */
    protected $_code = self::CODE;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canAuthorize = true;
    
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;
    
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefund = true;
    
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
    
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canUseInternal = false;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * Bank Transfer payment block paths
     *
     * @var string
     */
    protected $_formBlockType = \Webkul\MpWalletSystem\Block\Form\Walletsystem::class;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                            $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                    $logger
     * @param \Magento\Store\Model\StoreManagerInterface              $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * Authorize payment.
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float                                                                      $amount
     *
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }
    
    /**
     * Get Configuration of Payment Action
     *
     * @return mixed
     */
    public function getConfigPaymentAction()
    {
        return $this->_scopeConfig->getValue(
            'payment/mpwalletsystem/payment_action',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getStoreId()
        );
    }

    /**
     * Is Available function
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return int
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->helper->getPaymentisEnabled();
    }

    /**
     * Get Loader Image Url
     *
     * @return string
     */
    public function getLoaderImage()
    {
        return $this->getViewFileUrl('Webkul_MpWalletSystem::images/loader.gif');
    }
}
