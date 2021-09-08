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

namespace Webkul\MpWalletSystem\Plugin\Marketplace\Block\Order;

class Totals
{
    /**
     * Initialize dependencies
     *
     * @param \Webkul\Marketplace\Model\Orders $mpOrder
     * @param \Webkul\Marketplace\Helper\Data  $helper
     */
    public function __construct(
        \Webkul\Marketplace\Model\Orders $mpOrder,
        \Webkul\Marketplace\Helper\Data $helper
    ) {
        $this->mpOrder = $mpOrder;
        $this->helper = $helper;
    }
    
    /**
     * After plugin of getOrderTotals function
     *
     * @param \Webkul\Marketplace\Block\Order\Totals $subject
     * @param array $result
     * @return array
     */
    public function afterGetOrderTotals(
        \Webkul\Marketplace\Block\Order\Totals $subject,
        $result
    ) {
        $orderId = $subject->getRequest()->getParam('order_id');
        $invoiceId = $subject->getRequest()->getParam('invoice_id');
        $magentoOrder = $subject->getOrder();
        $orderCollection = $this->mpOrder->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('invoice_id', $invoiceId);
        foreach ($orderCollection as $order) {
            if ($order->getWalletAmount()) {
                $walletAmount = $order->getWalletAmount();
                $source = $subject->getSource();
                $currencyRate = $source[0]['currency_rate'];
                $result['wallet_amount'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'wallet_amount',
                        'strong' => 1,
                        'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $walletAmount),
                        'label' => __('Amount Credited To Seller Wallet')
                    ]
                );
            }
        }
        return $result;
    }
}
