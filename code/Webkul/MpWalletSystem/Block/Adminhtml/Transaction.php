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

namespace Webkul\MpWalletSystem\Block\Adminhtml;

use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Magento\Sales\Model\Order;

/**
 * Webkul MpWalletSystem Block
 */
class Transaction extends \Magento\Backend\Block\Template
{
    /**
     * @var Order
     */
    private $order;
    
    /**
     * @var [WallettransactionFactory]
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
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param Order                                             $order
     * @param WallettransactionFactory                          $wallettransactionFactory
     * @param \Magento\Customer\Model\CustomerFactory           $customerFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Webkul\MpWalletSystem\Helper\Data                $helper
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Order $order,
        WallettransactionFactory $wallettransactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->order = $order;
        $this->walletTransaction = $wallettransactionFactory;
        $this->customerFactory = $customerFactory;
        $this->priceCurrency = $priceCurrency;
        $this->walletHelper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get order object
     *
     * @return object
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get transaction data
     *
     * @return object
     */
    public function getTransactionData()
    {
        $id = $this->getRequest()->getParam('entity_id');
        return $this->walletTransaction->create()->load($id);
    }

    /**
     * Get customer data by id
     *
     * @param int $customerId
     * @return object
     */
    public function getCustomerDataById($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }

    /**
     * Get transaction amount
     *
     * @param object $transactionData
     * @return string
     */
    public function getTransactionAmount($transactionData)
    {
        $amount = $transactionData->getCurrAmount();
        $currencyCode = $transactionData->getCurrencyCode();
        $precision = 2;
        return $this->priceCurrency->format(
            $amount,
            $includeContainer = true,
            $precision,
            $scope = null,
            $currencyCode
        );
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
    
    /**
     * Return Wallet helper object
     *
     * @return object \Webkul\MpWalletSystem\Helper\Data
     */
    public function getWalletHelper()
    {
        return $this->walletHelper;
    }
}
