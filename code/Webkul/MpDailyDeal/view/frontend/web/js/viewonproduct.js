/**
 * Webkul_MpDailyDeals Category View Js
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/translate",
    "underscore",
    "jquery/ui"
], function ($, $t, _) {
    "use strict";
    $.widget('mage.viewondealproduct', {
        _create: function () {
            self = this;
            $('.wk-dd-bundle-selection').each(function(ind, val) {
                if($('#bundle-option-'+$(val).data('selection')+'-container').length) {
                    $('#bundle-option-'+$(val).data('selection')+'-container').after($(val));
                }else if($('#bundle-option-'+$(val).data('selection')).length) {
                    $('#bundle-option-'+$(val).data('selection')).after($(val));
                } else if($('[for=bundle-option-'+$(val).data('selection')+']').length) {
                    $('[for=bundle-option-'+$(val).data('selection')+']').after($(val));
                }
            });
            var viewCategoryOpt = self.options;
            var days    = 24*60*60,
            hours   = 60*60,
            minutes = 60;
            function main()
            {
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
                                message += '<span class="wk_front_dd_set_time_days wk-deal-clock-span" id="wk-deal-dd" title="'+$t("Days")+'">'+days+' ,<span class="label wk-deal-clock-label-dd" for="wk-deal-dd"><span>'+$t("Days")+'</span></span></span>';
                                message += '<span class="wk_front_dd_set_time wk-deal-clock-span" id="wk-deal-hr" title="'+$t("Hours")+'"> '+hours+' :<span class="label wk-deal-clock-label-hr" for="wk-deal-hr"><span>'+$t("Hours")+'</span></span></span>';
                                message += '<span class="wk_front_dd_set_time wk-deal-clock-span" id="wk-deal-mi" title="'+$t("Minutes")+'"> '+minutes+' :<span class="label wk-deal-clock-label-mi" for="wk-deal-mi"><span>'+$t("Minutes")+'</span></span></span>';
                                message += '<span class="wk_front_dd_set_time wk-deal-clock-span" id="wk-deal-sec" title="'+$t("Seconds")+'"> '+seconds+' <span class="label wk-deal-clock-label-sec" for="wk-deal-sec"><span>'+$t("Seconds")+'</span></span></span>';
                                colckElm.html(message); 
                                var dealdealid = dealBlock.attr('data-deal-id');
                                if (hours == 0 && minutes == 0 && seconds == 0) {
                                    $.ajax({
                                        url: window.BASE_URL+"mpdailydeal/index/updatedealinfo",
                                        data: {'deal-id': dealdealid},
                                        type: 'POST',
                                        dataType:'html',
                                        success: function (transport) {
                                            location.reload();
                                            var response = $.parseJSON(transport);
                                        }
                                    });
                                    var priceBox = dealBlock.prev('.price-box');
                                    if (priceBox.length ==0) {
                                        priceBox = dealBlock.prev('.product-info-price').find('.price-box');
                                    }
                                    priceBox.find('.special-price').remove();
                                    priceBox.find('.price-label').remove();
                                    dealBlock.remove();
                                    priceBox.find('.old-price').addClass('price').removeClass('old-price');
                                }
                            }
                        });
                    }
                });
            }
            $('body').on('wkdailydealLoaded', function () {
                main();
            });
            if (window.wkdailydealLoaded!=undefined && window.wkdailydealLoaded) {
                main();
            }
            $(document).ready(function () {
                if ($('.wk-grouped-deal').length > 0) {
                    $('.wk-grouped-deal').each(function () {
                        var id = $(this).attr('data-id');
                        $('[data-product-id='+id+']').append($('#grouped'+id));
                    });
                }
                if (viewCategoryOpt.dealstatus) {
                    var text = $('.product-info-main .product-info-price .price-box .special-price .price-container > span.price-label').text();
                    if (text != 'Deal Price') {
                        $('.product-info-main .product-info-price .price-box .special-price .price-container > span.price-label').text($t('Deal Price'));
                    }
                }
            });
            $(".product-options-wrapper div").click(function (e) {
                var selected_options = {};
                $('div.swatch-attribute').each(function (k,v) {
                 var attribute_id    = $(v).attr('attribute-id');
                    if(attribute_id==undefined)
                    attribute_id    =   $(v).attr("data-attribute-id");

                    var option_selected = $(v).attr('option-selected');
                    if(option_selected==undefined)
                    {
                        option_selected = $(v).attr('data-option-selected');
                    }
                    if (!attribute_id || !option_selected) {return;}
                    selected_options[attribute_id] = option_selected;
                });
               
                if ($('[data-role=swatch-options]').length) {
                    var product_id_index = $('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index;
                    var found_ids = [];
                    $.each(product_id_index, function (product_id,attributes) {
                     var productIsSelected = function (attributes, selected_options) {
                            return _.isEqual(attributes, selected_options);
                        }
                        if (productIsSelected(attributes, selected_options)) {
                            found_ids.push(product_id);
                        }
                    });
                    var selectedProductId = found_ids[0];
                     $('.wk-daily-deal').hide();
                    $('.deal-price-label').remove();
                    if ($('#deal'+selectedProductId).length==1) {
                        $('#deal'+selectedProductId).show();
                        $(".product-info-main .product-info-price .normal-price .price-container").prepend('<span class="price-label deal-price-label">Deal Price</span>');
                    }
                }
            });
        }
    });
    return $.mage.viewondealproduct;
});