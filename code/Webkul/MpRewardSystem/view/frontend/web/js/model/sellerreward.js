/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
define(
    ['ko'],
    function (ko) {
        'use strict';
        var rewardData = window.checkoutConfig.rewards;
        var rewardSession = window.checkoutConfig.rewardSession;
        var rewardStatus = window.checkoutConfig.rewardStatus;
        return {
            rewardData: rewardData,
            rewardSession: rewardSession,
            rewardStatus: rewardStatus,

            getRewardData: function () {
                return rewardData;
            },

            getRewardSession: function () {
                return rewardSession;
            },

            getRewardStatus: function () {
              return rewardStatus;
            },
        };
    }
);
