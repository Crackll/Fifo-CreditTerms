/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    "jquery/ui"
], function ($, $t, mageTemplate, alert, modal) {
    'use strict';
    $.widget('mage.verifyUserName', {
        options: {
            backUrl: '',
            userName: '[data-role="user-name"]',
            becomeWholeSalerBoxWrapper: '[data-role="wk-mp-become-wholesaler-box-wrapper"]',
            available: '.available',
            unavailable: '.unavailable',
            pageLoader: '#wk-load'
        },
        _create: function () {
            var self = this;
            $('body').delegate(
                '#wholesaler_term_light',
                'click',
                function (e) {
                    e.preventDefault();
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        width:'200px',
                        title: self.options.headingData,
                        buttons: [{
                            text: $.mage.__('Ok'),
                            class: '',
                            click: function () {
                                this.closeModal();
                            }
                        }]
                    };
                    var cont = $('#wholesaler-term').html();
                    cont = $('<div />').append(cont);
                    modal(options, cont);
                    cont.modal('openModal');
                }
            );
            $(this.element).delegate(self.options.userName, 'keyup', function () {
                var userNameVal = $(this).val();
                $(self.options.userName).val(userNameVal.replace(/[^a-z^A-Z^0-9\.\-]/g,''));
            });
            $(this.element).delegate(self.options.userName, 'change', function () {
                self.callAjaxFunction();
            });
        },
        callAjaxFunction: function () {
            var self = this;
            $(self.options.button).addClass('disabled');
            var userNameVal = $(self.options.userName).val();
            $(self.options.available).remove();
            $(self.options.unavailable).remove();
            if (userNameVal) {
                $(self.options.pageLoader).removeClass('no-display');
                $.ajax({
                    type: "POST",
                    url: self.options.ajaxSaveUrl,
                    data: {
                        username: userNameVal
                    },
                    success: function (response) {
                        $(self.options.pageLoader).addClass('no-display');
                        if (response===0) {
                            $(self.options.button).removeClass('disabled');
                            $(self.options.becomeWholeSalerBoxWrapper).append(
                                $('<div/>').addClass('available message success')
                                .text(self.options.successMessage)
                            );
                        } else {
                            $(self.options.button).addClass('disabled');
                            $(self.options.userName).val('');
                            $(self.options.becomeWholeSalerBoxWrapper).append(
                                $('<div/>').addClass('available message error')
                                .text(self.options.errorMessage)
                            );
                        }
                    },
                    error: function (response) {
                        alert({
                            content: $t('There was error during verifying user name data')
                        });
                    }
                });
            }
        }
    });
    return $.mage.verifyUserName;
});
