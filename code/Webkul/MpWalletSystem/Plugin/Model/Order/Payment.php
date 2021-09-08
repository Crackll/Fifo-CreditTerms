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

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class Payment
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        \Magento\Sales\Model\Order $order
    ) {
        $this->order = $order;
    }

    /**
     * After Get Title function
     *
     * @param \Magento\Sales\Model\Order\Payment $subject
     * @param string $result
     * @return string $result
     */
    public function afterGetTitle(
        \Magento\Sales\Model\Order\Payment $subject,
        $result
    ) {
    
        $orderId = $subject->getParentId();
        $order = $this->order->load($orderId);
        if (-$order->getWalletAmount() > 0) {
            if ($result != __('Webkul MpWallet System')) {
                return $result.' + Webkul MpWallet System';
            }
        }
        return $result;
    }
}
