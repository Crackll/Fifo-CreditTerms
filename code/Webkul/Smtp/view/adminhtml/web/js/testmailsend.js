
/**
 * Webkul_Smtp test mail Js
 * @category  Webkul
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('smtp.sendmailtest', {
        _create: function () {
            var options = this.options;
            $("#smtp_send_test_mail_button").on("click", function () {
                var alerttext = '';
                new Ajax.Request(options.mailsendurl, {
                    method: 'post',
                    parameters: {
                        'mail-from':$('#smtp_test_mail_from').val(),
                        'mail-to':$('#smtp_test_mail_to').val(),
                        'content':$('#smtp_test_content').val()
                    },
                    onSuccess: function (transport) {
                        var response = $.parseJSON(transport.responseText);
                        if (response.msg) {
                            $('<div />').html(response.msg)
                                .modal({
                                    title: $.mage.__('Attention'),
                                    autoOpen: true,
                                    buttons: [{
                                     text: 'OK',
                                        attr: {
                                            'data-action': 'cancel'
                                        },
                                        'class': 'action-primary',
                                        click: function () {
                                                this.closeModal();
                                            }
                                    }]
                                 });
                        } else {
                            $('<div />').html(alerttext)
                                .modal({
                                    title: $.mage.__('Attention'),
                                    autoOpen: true,
                                    buttons: [{
                                     text: 'OK',
                                        attr: {
                                            'data-action': 'cancel'
                                        },
                                        'class': 'action-primary',
                                        click: function () {
                                                this.closeModal();
                                            }
                                    }]
                            });
                        }
                    }
                });
            });
        }
    });
    return $.smtp.sendmailtest;
});
