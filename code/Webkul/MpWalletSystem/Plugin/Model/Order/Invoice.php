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

namespace Webkul\MpWalletSystem\Plugin\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class Invoice extends \Magento\Sales\Model\Order\Invoice
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Model\Context                                           $context
     * @param \Magento\Framework\Registry                                                $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory                          $extensionFactory
     * @param AttributeValueFactory                                                      $customAttributeFactory
     * @param \Magento\Sales\Model\Order\Invoice\Config                                  $invoiceConfig
     * @param \Magento\Sales\Model\OrderFactory                                          $orderFactory
     * @param \Magento\Framework\Math\CalculatorFactory                                  $calculatorFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory    $invoiceItemCollectionFactory
     * @param \Magento\Sales\Model\Order\Invoice\CommentFactory                          $invoiceCommentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $commentCollectionFactory
     * @param \Webkul\MpWalletSystem\Helper\SplitPayments                                $splitPayments
     * @param \Webkul\MpWalletSystem\Helper\Mail                                         $mailHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource                    $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb                              $resourceCollection
     * @param array                                                                      $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\Order\Invoice\Config $invoiceConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Math\CalculatorFactory $calculatorFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $invoiceItemCollectionFactory,
        \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $commentCollectionFactory,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayments,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $invoiceConfig,
            $orderFactory,
            $calculatorFactory,
            $invoiceItemCollectionFactory,
            $invoiceCommentFactory,
            $commentCollectionFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->splitPayments = $splitPayments;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Pay function
     *
     * @return this
     */
    public function pay()
    {
        if ($this->_wasPayCalled) {
            return $this;
        }
        $this->_wasPayCalled = true;

        $this->setState(self::STATE_PAID);

        $order = $this->getOrder();
        $order->getPayment()->pay($this);
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $item) {
            if ($item->getSku() == "wk_wallet_amount") {
                $orderState = Order::STATE_COMPLETE;
                $order->setState($orderState)->setStatus(Order::STATE_COMPLETE);
                $splitPaymentsHelper = $this->splitPayments;
                $splitPaymentsHelper->commitMethod($order);
                $mailHelper = $this->mailHelper;
                $mailHelper->checkAndUpdateWalletAmount($order);
            }
        }
        $totalPaid = $this->getGrandTotal();
        $baseTotalPaid = $this->getBaseGrandTotal();
        $invoiceList = $order->getInvoiceCollection();
        // calculate all totals
        if (count($invoiceList->getItems()) > 1) {
            $totalPaid += $order->getTotalPaid();
            $baseTotalPaid += $order->getBaseTotalPaid();
        }
        $order->setTotalPaid($totalPaid);
        $order->setBaseTotalPaid($baseTotalPaid);
        $this->_eventManager->dispatch('sales_order_invoice_pay', [$this->_eventObject => $this]);
        return $this;
    }
}
