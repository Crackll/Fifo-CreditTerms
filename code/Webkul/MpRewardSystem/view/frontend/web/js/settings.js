/**
 * MpRewardSystem
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    "jquery/ui"
], function ($, $t) {
    'use strict';
    $.widget('mage.settings', {
        _create: function () {
            var self = this;
            var addDetailsForm = $(self.options.detailsForm);
            addDetailsForm.mage('validation', {});
            var rewardSetType = $(self.options.rewardSets).val();
            if (rewardSetType != 0) {
                $(self.options.rewardStatus).parents('.field').hide();
            } else {
                $(self.options.rewardStatus).parents('.field').show();
            }
            $(self.options.rewardSets).on('change',function (e) {
                var rewardSetType = $(this).val();
                if (rewardSetType != 0) {
                    $(self.options.rewardStatus).parents('.field').hide();
                } else {
                    $(self.options.rewardStatus).parents('.field').show();
                }
            })
        }
    });
    return $.mage.settings;
});
