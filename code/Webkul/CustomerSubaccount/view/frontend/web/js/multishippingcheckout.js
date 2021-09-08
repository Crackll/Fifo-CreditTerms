/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery",
    'mage/translate'
], function ($, $t) {
    'use strict';
    return function (option) {
        var interval = setInterval(() => {
            $('.multishipping-checkout-shipping .box-shipping-address a.action.edit').remove()
            $('.multishipping-checkout_address-selectbilling .box-billing-address a.action.edit').remove()
            $('.multishipping-checkout-overview .box-billing-address a.action.edit').remove()
            $('.multishipping-checkout-overview .box-shipping-address a.action.edit').remove()  
            $('.multishipping-checkout-addresses button.action.add').remove()
        }, 100);
        setTimeout(() => {
            clearInterval(interval)
        }, 3000);
    }
});
    
