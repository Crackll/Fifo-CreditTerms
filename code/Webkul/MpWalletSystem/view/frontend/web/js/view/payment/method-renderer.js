/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Magento_Checkout/js/model/cart/totals-processor/default'
    ],
    function (
        Component,
        rendererList,
        defaultTotal
    ) {
        'use strict';
        defaultTotal.estimateTotals();
        rendererList.push(
            {
                type: 'mpwalletsystem',
                component: 'Webkul_MpWalletSystem/js/view/payment/method-renderer/mpwalletsystem'
            }
        );

        return Component.extend(
            {

            }
        );
    }
);