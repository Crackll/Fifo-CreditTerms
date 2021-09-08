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

namespace Webkul\MpWalletSystem\Block;

use Webkul\MpWalletSystem\Model\ResourceModel\Wallettransaction;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Webkul\MpWalletSystem\Model\ResourceModel\Walletrecord;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Webkul\MpWalletSystem\Model\WalletPayeeFactory;

/**
 * Webkul MpWalletSystem Block
 */
class Transfer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\MpWalletSystem\Model\ResourceModel\Wallettransaction
     */
    private $wallettransactionModel;
    
    /**
     * @var payeeCollection
     */
    private $payeeCollection;
    
    /**
     * @var Webkul\MpWalletSystem\Model\ResourceModel\Walletrecord
     */
    private $walletrecordModel;
    
    /**
     * @var Order
     */
    private $order;
    
    /**
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    private $walletHelper;
    
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;
    
    /**
     * @var Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollection;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WallettransactionFactory
     */
    private $walletTransaction;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;
    
    /**
     * @var $walletRecordData
     */
    private $walletRecordData = null;

    /**
     * @var $walletPayee
     */
    protected $walletPayee;

    /**
     * Initialize dependencies
     *
     * @param MagentoFrameworkViewElementTemplateContext    $context
     * @param WalletrecordCollectionFactory                 $walletrecordModel
     * @param WallettransactionCollectionFactory            $wallettransactionModel
     * @param Order                                         $order
     * @param WebkulWalletsystemHelperData                  $walletHelper
     * @param MagentoFrameworkPricingHelperData             $pricingHelper
     * @param CustomerCollection                            $customerCollection
     * @param WallettransactionFactory                      $wallettransactionFactory
     * @param MagentoCustomerModelCustomerFactory           $customerFactory
     * @param MagentoFrameworkPricingPriceCurrencyInterface $priceCurrency
     * @param array                                        $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Walletrecord\CollectionFactory $walletrecordModel,
        Wallettransaction\CollectionFactory $wallettransactionModel,
        Order $order,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        CustomerCollection $customerCollection,
        WallettransactionFactory $wallettransactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        WalletPayeeFactory $walletPayee,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->walletrecordModel = $walletrecordModel;
        $this->wallettransactionModel = $wallettransactionModel;
        $this->order = $order;
        $this->walletHelper = $walletHelper;
        $this->pricingHelper = $pricingHelper;
        $this->customerCollection = $customerCollection;
        $this->walletTransaction = $wallettransactionFactory;
        $this->customerFactory = $customerFactory;
        $this->priceCurrency = $priceCurrency;
        $this->walletPayee = $walletPayee;
    }

    /**
     * Prepare layout
     *
     * @return this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getWalletPayeeCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'walletsystem.walletpayee.pager'
            )
                ->setCollection(
                    $this->getWalletPayeeCollection()
                );
            $this->setChild('pager', $pager);
            $this->getWalletPayeeCollection()->load();
        }

        return $this;
    }

    /**
     * Get Pager Html function
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get wallet record data
     *
     * @param int $customerId
     * @return object
     */
    public function getWalletRecordData($customerId)
    {
        if ($this->walletRecordData==null) {
            $walletRecordCollection = $this->walletrecordModel->create()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($walletRecordCollection->getSize()) {
                foreach ($walletRecordCollection as $record) {
                    $this->walletRecordData = $record;
                    break;
                }
            }
        }
        return $this->walletRecordData;
    }

    /**
     * Get remaining total of a customer
     *
     * @param int $customerId
     * @return void
     */
    public function getWalletRemainingTotal($customerId)
    {
        $remainingAmount = 0;
        $walletRecord = $this->getWalletRecordData($customerId);
        if ($walletRecord && $walletRecord->getEntityId()) {
            $remainingAmount = $walletRecord->getRemainingAmount();
            return $this->pricingHelper
                ->currency($remainingAmount, true, false);
        }
        return $this->pricingHelper
            ->currency(0, true, false);
    }

    /**
     * Get transaction collection of a customer
     *
     * @return void
     */
    public function getWalletPayeeCollection()
    {
        if (!$this->payeeCollection) {
            $customerId = $this->walletHelper
                ->getCustomerId();
            $walletPayeeCollection = $this->walletPayee->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', $customerId);
            $this->payeeCollection = $walletPayeeCollection;
        }
        return $this->payeeCollection;
    }

    /**
     * Get order
     *
     * @return object
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get wallet payee collection
     *
     * @return collection
     */
    public function getEnabledPayeeCollection()
    {
        $customerId = $this->walletHelper
            ->getCustomerId();
        $walletPayee = $this->walletPayee->create();
        $walletPayeeCollection = $this->walletPayee->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', $walletPayee::PAYEE_STATUS_ENABLE);
        return $walletPayeeCollection;
    }

    /**
     * Load customer model by customer id
     *
     * @param int $customerId
     * @return object
     */
    public function getCustomerDataById($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }

    /**
     * Get formatted date
     *
     * @param Date $date
     * @return Date
     */
    public function getFormattedDate($date)
    {
        return $this->localeDate->date(new \DateTime($date));
    }
}
