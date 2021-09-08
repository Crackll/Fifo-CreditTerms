/**
 * Webkul_MpDailyDeals Deal list Js
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('mage.sellerDealList', {
        _create: function () {
            var timeoffset = this.options.timeOffset;
            var zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            $('#mpdealselecctall').change(function () {
                if ($(this).is(":checked")) {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', true);
                    });
                } else {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', false);
                    });
                }
            });

            $('#form-productlist-massdisable').submit(function () {
                if ($('.mpcheckbox:checked').length == 0) {
                    alert('please select product for disable deal.');
                    return false;
                }
            });

            $('.mpcheckbox').change(function () {
                if ($(this).is(":checked")) {
                    var totalCheck = $('.wk-row-view  .mpcheckbox').length,
                        totalCkecked = $('.wk-row-view  .mpcheckbox:checked').length;
                    if (totalCheck == totalCkecked ) {
                        $('#mpdealselecctall').prop('checked', true);
                    }
                } else {
                    $('#mpdealselecctall').prop('checked', false);
                }
            });
            $.each(timeoffset, function (key, data) {
                var stopTime = converttoTz($('#deal_to_date'+key).text(), data.deal_to_date);
                var startTime = converttoTz($('#deal_from_date'+key).text(), data.deal_from_date);
                $('#deal_to_date'+key).text('');
                $('#deal_from_date'+key).text('');
                $('#deal_to_date'+key).text(stopTime);
                $('#deal_from_date'+key).text(startTime);
            });
            function converttoTz(time, offset)
            {
                offset = 0;
                var currentoffset = new Date().getTimezoneOffset() * -1 * 60000;
                var date = new Date(new Date(time).getTime() + currentoffset + (-1000 * parseInt(offset)));
                var d,m,h,i,s;
                d = date.getDate();
                m = date.getMonth();
                h = date.getHours();
                i = date.getMinutes();
                s = date.getSeconds();
                if (parseInt(d) < 10) {
                        d = "0"+d;
                }
                if (parseInt(m) < 10) {
                        m = "0"+m;
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
                return (parseInt(m)+1)+'/'+d+'/'+date.getFullYear()+' '+h+':'+i+':'+s;
            }

        }
    });
    return $.mage.sellerDealList;
});