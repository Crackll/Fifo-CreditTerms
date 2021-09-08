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

namespace Webkul\MpWalletSystem\Plugin\Model;

/**
 * Class DiscountConfigureProcess
 *
 * Removes discount block when wallet amount product is in cart.
 */
class Order
{
    /**
     * After Get Grand Total function
     *
     * @param \Magento\Sales\Model\Order\Payment $subject
     * @param string $result
     * @return string $result
     */
    public function afterGetGrandTotal(\Magento\Sales\Model\Order $subject, $result)
    {
        $source = $subject;
        if (!$subject->getBaseTotalInvoiced()) {
            return $amount = $source->getSubtotalInclTax() + (int) $source->getShippingAmount();
        } else {
            return $result;
        }
    }
}
