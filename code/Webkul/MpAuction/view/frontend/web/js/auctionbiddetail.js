/**
 * Webkul_MpAuction Product View Js
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery",
    "mage/translate",
    "jquery/ui"
], function ($, $t) {
    "use strict";
    $.widget('auction.auctiondetail', {
        _create: function () {
            $(document).ready(function () {
                var token = true;
                if ((window.location.href).indexOf("#mp-bid-details") >= 0 || (window.location.href).indexOf("#normal-bid-record") >= 0) {
                    setTimeout(function () {
                        $('#tab-label-normal-bid-record').addClass('active');
                        $('#normal-bid-record').css('display','block');
                    }, 1);
                }
                 if ((window.location.href).indexOf("#automatic-bid-record") >= 0) {
                    setTimeout(function () {
                        $('#tab-label-automatic-bid-record').addClass('active');
                        $('#automatic-bid-record').css('display','block');
                    }, 1);
                }
                $('#tab-label-mp-bid-details-title').click(function () {
                    if (token) {
                        setTimeout(function () {
                            $('#tab-label-normal-bid-record').addClass('active');
                            $('#normal-bid-record').css('display','block');
                        }, 1);
                    }
                });
                $('#tab-label-normal-bid-record-title').click(function () {
                    token = false;
                    $('#tab-label-mp-bid-details-title').trigger('click');
                    setTimeout(function () {
                        $('#tab-label-mp-bid-details,#tab-label-normal-bid-record').addClass('active');
                        $('#mp-bid-details,#automatic-bid-record').css('display','none');
                        $('#mp-bid-details,#normal-bid-record').css('display','block');
                        $('#tab-label-automatic-bid-record').removeClass('active');
                        token = true;
                    }, 1);
                });
                $('#tab-label-automatic-bid-record-title').click(function () {
                    token = false;
                    $('#tab-label-mp-bid-details-title').trigger('click');
                    setTimeout(function () {
                        $('#tab-label-mp-bid-details,#tab-label-automatic-bid-record').addClass('active');
                        $('#tab-label-normal-bid-record').removeClass('active');
                        $('#mp-bid-details,#normal-bid-record').css('display','none');
                        $('#mp-bid-details,#automatic-bid-record').css('display','block');
                        token = true;
                    }, 1);
                });
                
                //var startTime = converttoTz($('#start_auction_time'+key).text(), data.start_auction_time);

                function converttoTz(time, offset)
                {
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
            });
        }
    });
    return $.auction.auctiondetail;
});
