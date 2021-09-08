/**
 * Webkul_MpDailyDeals Category View Js
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
requirejs([
    "jquery",
    "mage/translate",
    "jquery/ui"
], function ($, $t) {
    var days    = 24*60*60,
    hours   = 60*60,
    minutes = 60;
    $.fn.countdown = function (prop) {
        var options = $.extend({
            callback    : function () {
                            alert("");},
            timestamp   : 0
        },prop);
        var left, d, h, m, s, positions;
        positions = this.find('.position');
        var initialize =  setInterval(function () {
            left = Math.floor((options.timestamp - (new Date())) / 1000);
            if (left < 0) {
                left = 0;
            }
            d = Math.floor(left / days);
            left -= d*days;
            h = Math.floor(left / hours);
            left -= h*hours;
            m = Math.floor(left / minutes);
            left -= m*minutes;
            s = left;
            options.callback(d, h, m, s);
            if (d==0 && h==0 && m==0 && s==0) {
                clearInterval(initialize);
            }
        }, 1000);
        return this;
    };

    $(document).ready(function () {
        $('.deal.wk-daily-deal').each(function () {
            var dealBlock = $(this),
                colckElm  = dealBlock.find('.wk_cat_count_clock'),
                timeStamp = new Date(2012, 0, 1),
                stopTime  = colckElm.attr('data-stoptime'),
                newYear   = true;
            if ((new Date()) > timeStamp) {
                timeStamp = colckElm.attr('data-diff-timestamp')*1000;
                timeStamp = (new Date()).getTime() + timeStamp;
                newYear = false;
            }
            if (colckElm.length) {
                colckElm.countdown({
                    timestamp : timeStamp,
                    callback : function (days, hours, minutes, seconds) {
                        var message = "",
                            timez = "",
                            distr = stopTime.split(' '),
                            tzones =  distr[0].split('-'),
                            months = [
                                'January',
                                'February',
                                'March',
                                'April',
                                'May',
                                'June',
                                'July',
                                'August',
                                'September',
                                'October',
                                'November',
                                'December'
                            ];
                        if (hours < 10) {
                            hours = "0"+hours;}
                        if (minutes < 10) {
                            minutes = "0"+minutes;}
                        if (seconds < 10) {
                            seconds = "0"+seconds;}
                        message += "<span class='wk_set_time' title='Days'>"+days + "</span> "+$t(' Days ');
                        message += "<span class='wk_set_time' title='Hours'>"+hours + "</span>:";
                        message += "<span class='wk_set_time' title='Minutes'>"+minutes + "</span>:";
                        message += "<span class='wk_set_time' title='Seconds'>"+seconds + "</span> ";
                        colckElm.html(message);
                        if (hours == 0 && minutes == 0 && seconds == 0) {
                            location.reload();
                        }
                    }
                });
            }
        });

        if ($('.wk-grouped-deal').length > 0) {
            $('.wk-grouped-deal').each(function () {
                var id = $(this).attr('data-id');
                $('[data-product-id='+id+']').append($('#grouped'+id));
            });
        }
        $('.products-grid .product-item-info')
        .mouseenter(function () {
            if ($(document).width() >= 625) {
                $(this).find('.wk-daily-deal').css({'position':'unset'});
                $(this).find('.wk-deal-off-box').css({'left':'0px'});
            }
        })
        .mouseleave(function () {
            if ($(document).width() >= 625) {
                $(this).find('.wk-daily-deal').css({'position':'relative'});
                $(this).find('.wk-deal-off-box').css({'left':'2px'});
            }
        })
    })
});