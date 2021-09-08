/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
    'mage/calendar'
], function ($, $t, mageTemplate, Calendar) {
    'use strict';
    $.widget('mage.condition', {
        _create: function () {
            var self = this;

            if (document.getElementById("one_condition_date") != null) {
                $('#one_condition_date').datepicker({ dateFormat: 'yy-mm-dd' });
            }
            if (document.getElementById("all_condition_date") != null) {
                $('#all_condition_date').datepicker({ dateFormat: 'yy-mm-dd' });
            }

            $(".rule-action-container").delegate(".condition.action-type","change",function () {
                var this_this = $(this);
                var flag = 0;
                $(this).parents("tr").siblings().find(".action-type").each(function () {
                    if ($(this).find("option:selected").val() == this_this.val()) {
                        flag = 1;
                    }
                });
                if (flag != 1) {
                    var context = {field_name:$(this).parents("table").attr("for")};
                    var progressTmpl = mageTemplate(".condition."+$(this).val()),tmpl;
                    tmpl = progressTmpl({
                        data: context
                    });
                    $(this).parent().next().html(tmpl);
                } else {
                    $(this).parents('tr').remove();
                }
                if (document.getElementById("one_condition_date") != null) {
                    $('#one_condition_date').datepicker({ dateFormat: 'yy-mm-dd' });
                }
                if (document.getElementById("all_condition_date") != null) {
                    $('#all_condition_date').datepicker({ dateFormat: 'yy-mm-dd' });
                }

            });

            $(".add-action-btn").on("click",function () {
                var context = {field_name:$(this).parents("table").attr("for")};
                var progressTmpl = mageTemplate("."+$(this).attr("for")),tmpl;
                tmpl = progressTmpl({
                    data: context
                });
                $(this).parents(".rule-action-details").append(tmpl);
            });

            $(".rule-action-container").delegate(".delete-select-row","click",function () {
                $(this).parents('tr').remove();
            });

        }
    });
    return $.mage.condition;
});
