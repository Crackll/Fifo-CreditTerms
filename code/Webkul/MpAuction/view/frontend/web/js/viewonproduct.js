/**
 * Webkul_Auction Product View Js
 * @category  Webkul
 * @package   Webkul_Auction
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery",
    "mage/translate",
    "jquery/ui",
    "mage/url"
], function ($,$t) {
    "use strict";
    var id=$("input[name=product]").val();
    $.widget('auction.productview', {
        _create: function () {
            var newtimestamp;
            var currenttimestamp;
            $('.wk-auction-view-bid-link').click(function () {
                $('#tab-label-mp-bid-details-title').trigger('click');
            });
            $('#bidding_amount').keypress(function (e) {
                var key, keychar;
                if (window.event) {
                    key = window.event.keyCode;
                } else if (e) {
                    key = e.which;
                } else {
                    return true;
                }
                keychar = String.fromCharCode(key);
                if ((key==null) || (key==0) || (key==8) ||
                    (key==9) || (key==13) || (key==27) || (key==46)) {
                    return true;
                } else if ((("0123456789").indexOf(keychar) > -1)) {
                    return true;
                } else {
                    return false;
                }
            });
            var viewProductOpt = this.options,
            days    = 24*60*60,
            hours   = 60*60,
            minutes = 60;
            $.fn.countdown = function (prop) {
                var options = $.extend({
                    callback    : function () {
                        alert("");
                    },
                    timestamp   : 0
                },prop);
                var left, d, h, m, s, positions;
                positions = this.find('.position');
                var initialize =  setInterval(function () {
                    left = (newtimestamp - currenttimestamp);
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
                    currenttimestamp += 1;
                }, 1000);
                return this;
            };
            $('.product-add-form').show();
            var clockOnPrice = viewProductOpt.auctionData.pro_auction,
                buyIdNow = viewProductOpt.auctionData.pro_buy_it_now,
                allowforbuy = $('#winner-data-container')
                                .hasClass('allow-for-buy');
            if (buyIdNow == 1 && !allowforbuy) {
                $('#product-addtocart-button span').text(viewProductOpt.buyItNow);
            } else if (allowforbuy) {
                var bidWinnerCart = $('#winner-data-container').attr('data-cart-label');
                $('#product-addtocart-button span').text(bidWinnerCart);
            } else if (viewProductOpt.auctionType!="") {
                $('.product-add-form').hide();
            }
           
            $('#product-addtocart-button').on('click', function () {
                setTimeout(function () {
                    if (buyIdNow == 1 && !allowforbuy) {
                        $('#product-addtocart-button span').text(viewProductOpt.buyItNow);
                    } else if (allowforbuy) {
                        var bidWinnerCart = $('#winner-data-container').attr('data-cart-label');
                        $('#product-addtocart-button span').text(bidWinnerCart);
                    } else if (viewProductOpt.auctionType!="") {
                        $('.product-add-form').hide();
                    }
                    $('.product-add-form').show();
                }, 7000);
            });
            function runClock() {
                $.ajax({
                    url: window.BASE_URL+"mpauction/account/check",
                    type: 'POST',
                    dataType:'html',
                    data: {
                        'id':id
                    },
                     success: function (response) {
                        var response = $.parseJSON(response);
                        if (response!=null) {
                            var note =  $('.wk_front_dd_countdownnew');
                            var ts = response.current;
                            newtimestamp = response.stopauctiontime;
                            currenttimestamp = response.current;
                            var newYear = true;
                            if (note.length) {
                                    if ((new Date()) > ts) {
                                        var t = response.difftime;
                                        ts = (new Date()).getTime() + t;
                                        newYear = false;
                                }
                                
                                note.countdown({
                                    timestamp : ts,
                                    callback : function (days, hours, minutes, seconds) {
                                        var stopt=response.stopauctiontime;
                                        //console.log(stopt);
                                        var stopt=viewProductOpt.stopt
                                        var message = "",
                                        months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                        if (days < 10) {
                                            days = "0"+days;
                                        }
                                        if (hours < 10) {
                                            hours = "0"+hours;
                                        }
                                        if (minutes < 10) {
                                            minutes = "0"+minutes;
                                        }
                                        if (seconds < 10) {
                                            seconds = "0"+seconds;
                                        }
                                        
                                        message += '<span class="wk_front_dd_set_time wk-auction-clock-span" id="wk-auction-dd" title='+$t("Days")+'>'+days+' ,<span class="label wk-auction-clock-label-dd" for="wk-auction-dd"><span>'+$t("Days")+'</span></span></span>';
                                        message += '<span class="wk_front_dd_set_time wk-auction-clock-span" id="wk-auction-hr" title='+$t("Hours")+'> '+hours+' :<span class="label wk-auction-clock-label-hr" for="wk-auction-hr"><span>'+$t("Hours")+'</span></span></span>';
                                        message += '<span class="wk_front_dd_set_time wk-auction-clock-span" id="wk-auction-mi" title='+$t("Minutes")+'> '+minutes+' :<span class="label wk-auction-clock-label-mi" for="wk-auction-mi"><span>'+$t("Minutes")+'</span></span></span>';
                                        message += '<span class="wk_front_dd_set_time wk-auction-clock-span" id="wk-auction-sec" title='+$t("Seconds")+'> '+seconds+' <span class="label wk-auction-clock-label-sec" for="wk-auction-sec"><span>'+$t("Seconds")+'</span></span></span>';
                                        // message += "("+ tzones[2]+' '+months[tzones[1]-1]+', '+tzones[0]+' '+ timez +")";
                                        note.html(message);
                                        
                                        if (hours == '00' && minutes == '00' && seconds == '00') {
                                            $.ajax({
                                                type: 'POST',
                                                url: viewProductOpt.cacheFlush,
                                                data: {
                                                    productId:viewProductOpt.auctionData.product_id
                                                },
                                                async : false,
                                                success: function (resultData) {
                                                    location.reload();
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    }
                });
            }
            if($(".wk-auction-clock-main-div").length!=0){
                runClock();
                $('.reviews-actions .action').on('click', function () {
                    $('.wk-auction-bids-record .product').removeClass('items');
                    setTimeout(function () {
                        $('.wk-auction-bids-record .product').addClass('items');
                    }, 1000);
                });
            }
            if ($('.wk_product_background').length>0) {
                let needReload = false;
                let mpauctionIdentifier = $('.wk_product_background').data('mpauction-blockid');
                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    dataType:'html',
                    data: {},
                    success: function (response) {
                        if ($(response).find('#winner-data-container').length > 0 || $(response).find('.wk_product_background').first().hasClass('wk-buy-it-now')) {
                            $('.product-add-form').attr("style", "display: block!important");
                        }
                        if ($(response).find('.wk_product_background').length>0) {
                            let actualMpauctionIdentifier = $(response).find('.wk_product_background').data('mpauction-blockid');
                            if (mpauctionIdentifier != actualMpauctionIdentifier) {
                                $('.wk_product_background').replaceWith($(response).find('.wk_product_background'));
                                var bidWinnerCart = $(response).find('#winner-data-container').attr('data-cart-label');
                                $('body').find('#product-addtocart-button span').text(bidWinnerCart);
                                runClock();
                            }
                        } else {
                            needReload = true;
                        }
                        if (needReload) {
                            $.ajax({
                                url: window.BASE_URL+"mpauction/account/dataUpdated",
                                type: 'GET',
                                dataType:'json',
                                data: {
                                    'id':id
                                },
                                success: function (response) {
                                    location.reload();
                                }
                            });
                        }
                        $('.wk_product_background').css('visibility','visible');
                    }
                });
            }
        }
    });

    return $.auction.productview;
});
