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
    'Magento_Ui/js/modal/alert',
    "Magento_Ui/js/modal/modal",
    'mage/translate'
], function ($, alert, modal, $t) {
    'use strict';
    return function (option) {
        $('.wkcs-popup-button').on('click', function(){
            alert({
                title: $(this).data('popup-title'),
                content: $(this).data('popup-data').replace(/,/g,'<br>'),
                modalClass: 'wkc-view-cart-popup',
            })
        })
    }
});
    
