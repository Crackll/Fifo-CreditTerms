
/**
 * Webkul_MpAuction autoauc.event
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
 /*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('autoauc.event', {
        options: {
            autoAuctionOpt: $('#wkauction_auto_auction_opt'),
            reservePriceElm: $('#wkauction_reserve_price'),
            reservePriceParentElm: $('#wkauction_reserve_price').parents('.field-reserve_price')
        },
        _create: function () {
        }
    });
    return $.autoauc.event;
});