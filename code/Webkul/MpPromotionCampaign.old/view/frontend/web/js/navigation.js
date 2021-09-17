/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery"
], function ($) {
    'use strict';
    return function (option) {
        var self = this;
        $('#mpcampaign_block').hide();
        $('body').on('mouseover', '.mpcampaign-marketplace-navigation', function () {
            $('#mpcampaign_block').show();
        });
        $('body').on('mouseleave', '.mpcampaign-marketplace-navigation', function () {
            $('#mpcampaign_block').hide();
        });
    }
});
    