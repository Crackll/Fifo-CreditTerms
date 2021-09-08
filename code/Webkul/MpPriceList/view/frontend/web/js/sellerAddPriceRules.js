/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui",
    'uiComponent',
    'mage/storage',
    'mage/url',
    'domReady!'
], function ($) {
    "use strict";
    $.widget('mppricelist.sellerAddPriceRules', {
        _create: function () {
            var self = this;
            var ruleApplyOn = this.options.ruleApplyOn;
            if (ruleApplyOn != "" && ruleApplyOn != 1) {
                $('#tab-label-mppricelist_rules').hide();
            }
            $(document).on('change',"[id='applicable_on']",function () {
                var selectedOption = $(this).val();
                if (selectedOption == 1) {
                    $('#tab-label-mppricelist_rules').show();
                    $("#tab-label-mppricelist_rules").focus();
                    $('.wk-pricelist-quantity').addClass('wk-no-display');
                    $('.wk-pricelist-total').addClass('wk-no-display');
                    $('.wk-pricelist-category').addClass('wk-no-display');
                }
                if (selectedOption == 2) {
                    $('#tab-label-mppricelist_rules').hide();
                    $('.wk-pricelist-category').removeClass('wk-no-display');
                    $('.wk-pricelist-quantity').addClass('wk-no-display');
                    $('.wk-pricelist-total').addClass('wk-no-display');
                 }
                if (selectedOption == 3) {
                    $('#tab-label-mppricelist_rules').hide();
                   $('.wk-pricelist-category').addClass('wk-no-display');
                   $('.wk-pricelist-quantity').removeClass('wk-no-display');
                   $('.wk-pricelist-total').addClass('wk-no-display');
                }
                if (selectedOption == 4) {
                    $('#tab-label-mppricelist_rules').hide();
                    $('.wk-pricelist-quantity').addClass('wk-no-display');
                    $('.wk-pricelist-category').addClass('wk-no-display');
                    $('.wk-pricelist-total').removeClass('wk-no-display');
                 }
            });
        },
    });
    return $.mppricelist.sellerAddPriceRules;
});