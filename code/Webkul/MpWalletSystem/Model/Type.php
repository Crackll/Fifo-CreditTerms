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

/**
 * Webkul MpWalletSystem Model Class
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve paid by options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Webkul\MpWalletSystem\Model\AdminWallet::PAY_TYPE_SHIPPING,
                'label' => __('Order Shipping By Wallet')
            ],
            [
                'value' => \Webkul\MpWalletSystem\Model\AdminWallet::PAY_TYPE_ORDER_AMOUNT,
                'label' => __('Order Amount Paid By Wallet')
            ],
            [
                'value' => \Webkul\MpWalletSystem\Model\AdminWallet::PAY_TYPE_ORDER_REFUND,
                'label' => __('Order Refund Paid By Wallet')
            ],
        ];
    }
}
