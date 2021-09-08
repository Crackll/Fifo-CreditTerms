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
    'mage/template'
], function ($, $t, mageTemplate) {
    'use strict';
    $.widget('mage.merge', {
        _create: function () {
            var self = this;
            $(document).ready(function () {
                $(".merge-ticket").attr("onClick", "");
            });

            $("body").delegate(".merge-ticket","click",function (e) {
                var progressTmpl = mageTemplate("#popup_container_template");
                $("body").append(progressTmpl);
                var ary = new Array();
                $('.admin__control-checkbox').each(function () {
                    if ($(this).is(":checked")) {
                        if (!isNaN($(this).val())) {
                            ary.push({ 'ticket_id': $(this).val()});
                            var content = {ticket_id : $(this).val()};
                            var progressTmpl = mageTemplate("#popup_body_template"), tmp;
                            tmp = progressTmpl({data:content});
                            $(".container-fluid").append(tmp);
                        }
                    }
                });

                if (ary.length==0) {
                    alert($t("You haven't selected any items!"));
                    return false;
                }
                $(".page-wrapper").css("opacity","0.35");
                $(".popup-body .row").eq(0).find("input").attr("checked","checked");
                $(".popup-body .row").eq(0).find(".wk_success").show();
                $(".popup-body .row").eq(0).find(".apply_response_btn").hide();
                $(".popup_container").eq(0).show();
            });

            $("body").delegate(".merge_checkbox","click",function () {
                $(".merge_checkbox").removeAttr("checked");
                $(this).attr("checked","checked");

                $(".wk_success").hide();
                $(".apply_response_btn").show();

                $(this).siblings(".wk_success").show();
                $(this).siblings(".apply_response_btn").hide();
            });

            $("body").delegate(".action_button.wk_primary","click",function (e) {
                e.preventDefault();
                if ($(".popup-body .row").length > 1) {
                    $("#wk_ts_merge_form").submit();
                } else {
                    $(".wk_ts_warning_msg").show();
                }
            })

            $("body").delegate(".close_pop_up,.popup_close_btn","click",function () {
                $(".page-wrapper").css("opacity","1");
                $(".popup_container").remove();
            });

            $("body").delegate(".action_button.apply_response_btn","click",function () {
                $(this).parents(".row").remove();
            })
        }
    });
    return $.mage.merge;
});
