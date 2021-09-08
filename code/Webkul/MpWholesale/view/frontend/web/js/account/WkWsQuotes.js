/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    "jquery/ui",
], function ($, $t, confirm) {
    'use strict';
    $.widget('mage.WkMsQuotes', {
        options: {
            confirmMessageForEditQuote: $t(' Are you sure you want to edit this quote ? '),
        },
        _create: function () {
            var self = this;
            $(self.options.wsquoteedit).on('click', function () {
                var element = $(this);
                var dicision = confirm({
                    title:$t('Edit Quote'),
                    content: self.options.confirmMessageForEditQuote,
                    actions: {
                        confirm: function () {
                            var $url=$(element).attr('data-url');
                            window.location = $url;
                        },
                    }
                });
            });
        }
    });
    return $.mage.WkMsQuotes;
});
