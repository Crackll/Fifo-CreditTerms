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
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function ($, $t, mageTemplate) {
    'use strict';
    $.widget('mage.holidays', {
        _create: function () {
            var self = this;

            $(".add-holiday-button-set button").on("click",function () {
                var flag = 0;
                $("#InvalidHoliday").hide();
                $("#HolidayValidation").hide();
                if ($("#holiday_name").val() == "") {
                    $("#InvalidHoliday").show();
                } else {
                    var date = $(".holidaylist-head").find(".months option:selected").text()+" "+$(".holidaylist-head").find(".days").val();
                    $(".holiday-date label").each(function () {
                        if ($(this).text() == date) {
                            flag = 1;
                        }
                    });
                    if (flag == 1) {
                        $("#HolidayValidation").show();
                    } else {
                        var name = $(".holidaylist-head").find("#holiday_name").val();
                        var context = {date:date, name:name, month:$(".holidaylist-head").find(".months").val(), day:$(".holidaylist-head").find(".days").val()};
                        var progressTmpl = mageTemplate($(".holidaylist").html()),tmpl;
                        tmpl = progressTmpl({
                            data: context
                        });
                        $(".holiday-list-container").append(tmpl);
                    }
                }
            });

            $(".holiday-list-container").delegate(".delete-icon","click",function () {
                $(this).parent().remove();
            });

            window.updateMonth= function (iMonth, iDay) {
                var el = document.getElementById(iDay);
                el.options.length = 0;
                for (var d = new Date(2013,iMonth-1,1); d.getMonth()==iMonth-1; d.setDate(d.getDate()+1)) {
                    var option = new Option(d.getDate(), d.getDate());
                    el.options[d.getDate()-1] = option;
                };
            };
        }
    });
    return $.mage.holidays;
});
