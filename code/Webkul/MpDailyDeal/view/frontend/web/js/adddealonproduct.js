/**
 * Webkul_MpDailyDeals Add Deal On Product Js
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui",
    "mage/calendar"
], function ($) {
    "use strict";
    $.widget('mage.addDealOnProduct', {
        _create: function () {
            var viewCategoryOpt = this.options;
            console.log(viewCategoryOpt);
            var zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if ($('#deal_to_date').val()) {
                var stopTime = converttoTz($('#deal_to_date').val(), viewCategoryOpt.stopOffset);
                var startTime = converttoTz($('#deal_from_date').val(), viewCategoryOpt.startOffset);
                $('#deal_to_date').val(stopTime);
                $('#deal_from_date').val(startTime);
            }
            function converttoTz(time, offset)
            {
                offset = 0;
                var currentoffset = new Date().getTimezoneOffset() * -1 * 60000;
                var date = new Date(new Date(time).getTime() + currentoffset);
                var d,m,h,i,s;
                d = date.getDate();
                m = date.getMonth();
                h = date.getHours();
                i = date.getMinutes();
                s = date.getSeconds();
                if (parseInt(d) < 10) {
                        d = "0"+d;
                }
                m = parseInt(m)+1;
                if (parseInt(m) < 10) {
                        m = "0"+parseInt(m);
                }
                if (parseInt(h) < 10) {
                    h = "0"+h;
                }
                if (parseInt(i) < 10) {
                    i = "0"+i;
                }
                if (parseInt(s) < 10) {
                    s = "0"+s;
                }
                return m+'/'+d+'/'+date.getFullYear()+' '+h+':'+i+':'+s;
            }

            $("#seller_time_zone").val(zone);
            $("#deal_from_date").datetimepicker({
                'dateFormat':'mm/dd/yy',
                'timeFormat':'HH:mm:ss',
                'minDate': new Date(),
                onClose: function ( selectedDate ) {
                    $("#deal_to_date").datetimepicker(
                        'option',
                        'minDate',
                        new Date(selectedDate)
                    );
                    $('#deal_to_date').val('');
                }
            });
            
            $("#deal_to_date").datetimepicker({
                                    'dateFormat':'mm/dd/yy',
                                    'timeFormat':'HH:mm:ss',
                                    'minDate': new Date(),
                                    onClose: function ( selectedDate ) {
                                        var from = $('#deal_from_date').datetimepicker("getDate");
                                        var to = $('#deal_to_date').datetimepicker("getDate");
                                        if (from == null || from > to) {
                                            alert('you can not select previous date from deal start date');
                                        }
                                    }
                                });

            $("#deal_status").change(function () {
                if ($(this).val()==0) {
                    $(".wk-mp-fieldset .control input").each(function () {
                        $(this).removeClass('required-entry');
                        $(this).parents('.field').removeClass('required');
                    });
                } else {
                    $(".wk-mp-fieldset .control input").each(function () {
                        $(this).addClass('required-entry');
                        $(this).parents('.field').addClass('required');
                    });
                }
            });
        }
    });
    return $.mage.addDealOnProduct;
});