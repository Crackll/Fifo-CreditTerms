/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/translate",
    "mage/mage",
    "Magento_Ui/js/modal/modal",
    "mage/calendar"
], function ($,$t,mage,modal,calendar) {
    'use strict';
    $.widget('mage.shipment', {
        _create: function () {
            var self = this;
            $('#scheduled-date').datepicker({
                dateFormat : "yy-mm-dd",
                changeMonth: true,
                changeYear :true,
                numberOfMonths: 1,
                showsTime: true,
                minDate: 0
            });
            var options = {
                type: 'popup',responsive: true,innerScroll: true,title: $t('Order Shipment'),
                buttons: [{
                        text: $t('Cancel'),
                        class:'wk-close-modal',
                        click: function () {
                            this.closeModal();
                        } //handler on button click
                    },{
                        text: $t('Ship'),
                        class: 'wk-shipment-submit',
                        click: function () {
                            if ($('#wk-shipment').validate()) {
                                $('#wk-shipment').submit();
                            }
                        } //handler on button click
                    }
                ]
            };
            var popup = modal(options, $('#popup_background'));
            $(self.options.button_id).on('click',function () {
                $('#popup_background').modal('openModal');
            });
        }
    });
    return $.mage.shipment;
});
