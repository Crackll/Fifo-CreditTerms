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

namespace Webkul\MpWalletSystem\Block\Sales\Order;

use Magento\Sales\Model\Order;
use Webkul\MpWalletSystem\Helper\Data;

/**
 * Webkul MpWalletSystem Block
 */
class WalletsystemInvoice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $order;
    
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;
    
    /**
     * Get data (totals) source model.
     *
     * @return \Magento\Framework\DataObject
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Model\Orders $order,
        \Webkul\MpWalletSystem\Model\AdminWallet $adminWallet,
        Data $walletHelper,
        array $data = []
    ) {
        $this->walletHelper = $walletHelper;
        $this->adminWallet = $adminWallet;
        $this->_marketplaceOrder = $order;
        parent::__construct($context, $data);
    }

    /**
     * Get source
     *
     * @return source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Display full summary
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }
    
    /**
     * Initialize all order totals.
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $invoice = $parent->getInvoice();
        $walletAmount = 0;
        $invoiceId = $invoice->getId();
        $walletAmountCollection = $this->_marketplaceOrder->getCollection()
            ->addFieldToFilter('invoice_id', $invoiceId);
        $this->order = $parent->getOrder();
        $baseCurrency = $this->walletHelper->getBaseCurrencyCode();
        $orderCurrency = $this->order->getOrderCurrencyCode();
        if ($walletAmountCollection->getSize()) {
            foreach ($walletAmountCollection as $order) {
                $walletAmount = $order->getWalletAmount();
            }
        } else {
            $adminWalletCollection = $this->adminWallet->getCollection()
                ->addFieldToFilter('order_id', $this->order->getId());
            foreach ($adminWalletCollection as $item) {
                $walletAmount = $item->getAmount();
            }
        }
        if ($walletAmount) {
                    $baseAmount = $this->walletHelper->getwkconvertCurrency(
                        $orderCurrency,
                        $baseCurrency,
                        $walletAmount
                    );
            $title = __('Amount Paid By Wallet');
                
            $walletPayment = new \Magento\Framework\DataObject(
                [
                    'code' => 'wallet_amount',
                    'strong' => false,
                    'value' => $walletAmount,
                    'base_value' => $baseAmount,
                    'label' => __($title),
                ]
            );
            $parent->addTotal($walletPayment, 'wallet_amount');
        }
        return $this;
    }
    
    /**
     * Get order store object.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->order->getStore();
    }
    
    /**
     * Get Order function
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Get Label Properties function
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get Value Properties function
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
