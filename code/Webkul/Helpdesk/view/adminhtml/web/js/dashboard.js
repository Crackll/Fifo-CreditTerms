/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
], function ($, $t, mageTemplate) {
    'use strict';
    $.widget('mage.dashboard', {
        _create: function () {
            var self = this;
            $(document).on("click",function (event) {
                event.stopPropagation();
                var className = event.target.className;
                var linkClassName = event.target.className.split(" ")[0];
                if (className != 'arrow_box' && linkClassName != 'wk_ts_pannel_heading') {
                    $(".arrow_box").hide();
                }
            });

            $('.wk_ts_pannel_heading').on("click",function () {
                $(".arrow_box").hide();
                $(this).find(".arrow_box").show();
            });
        }
    });
    return $.mage.dashboard;
});
