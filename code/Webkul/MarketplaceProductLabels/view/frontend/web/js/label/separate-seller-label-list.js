/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'Magento_Ui/js/modal/confirm'
], function ($, Component, $t, confirm) {
    'use strict';
    return Component.extend({
        initialize: function () {
            window.FORM_KEY = $("input[name=form_key]").val();
            this._super();
            var self = this;
            $("body").on("click", ".mp-edit", function () {
                var $url = $(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to edit this product label ? "),
                    actions: {
                        confirm: function () {
                            window.location = $url;
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            });
        }
    });
});
