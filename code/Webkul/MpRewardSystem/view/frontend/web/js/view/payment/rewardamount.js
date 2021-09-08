define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Catalog/js/price-utils',
        'Webkul_MpRewardSystem/js/model/sellerreward',
        'Webkul_MpRewardSystem/js/action/set-reward-action',
        'Webkul_MpRewardSystem/js/action/cancel-reward-action',
        'Magento_Checkout/js/view/summary/abstract-total'
    ],
    function (
        $,
        ko,
        Component,
        quote,
        totals,
        defaultTotal,
        priceUtils,
        sellerreward,
        setRewardAction,
        cancelRewardAction
    ) {
        'use strict';
        var totals = quote.getTotals();
        defaultTotal.estimateTotals();
        var isLoading = ko.observable(false);
        var changeValue = ko.observable();
        var userRewardPoint = ko.observable(null);
        var maxValue = ko.observable();
        var isVisible = ko.observable(false);
        var sellerReward = [];
        sellerReward = sellerreward.getRewardData();
        if (sellerreward.getRewardStatus() == 1) {
          var rewradStatus = ko.observable(true);
        } else {
          var rewradStatus = ko.observable(false);
        }
        var rewards = [];
        var session = [];

        $.each(sellerReward, function ( i, v) {
            rewards.push(v);
            maxValue(v.remaining_reward_point);
        });
        $.each(sellerreward.getRewardSession(), function ( i, v) {
            v.amount = priceUtils.formatPrice(Math.abs(v.amount), quote.getPriceFormat());
            v.avail_amount = priceUtils.formatPrice(v.avail_amount, quote.getPriceFormat());
            session.push(v);
        });
        var isApplied = ko.observable(session.length > 0);
        var rewardSession = ko.observableArray(session);
        return Component.extend({
            defaults: {
                template: 'Webkul_MpRewardSystem/checkout/rewardamount'
            },
            /**
             * Seller Reward list
             * @type {array of object}
             */
            sellerReward: rewards,

            changeValue:changeValue,

            maxValue:maxValue,
             /**
              * applied seller Reward list
              * @type {observable array}
              */
            rewardsession:rewardSession,

            userRewardPoint:userRewardPoint,
            /**
             * visible text box flag
             */
            isVisible: isVisible,

            isApplied: isApplied,
            /**
             * Loding flag
             */
            isLoading: isLoading,
            rewradStatus:rewradStatus,
            /**
             * Reward application procedure
             */
            initialize: function () {
                this._super();
                defaultTotal.estimateTotals();
            },
            /**
             * apply seller Reward
             */
            apply: function (value) {
                if (this.validate()) {
                    var formData = $('#reward-form').serialize();
                    isLoading(true);
                    setRewardAction(formData, isApplied, rewardSession, isLoading);
                }
            },
            /**
             * Cancel Reward session
             */
            cancel: function () {
                isLoading(true);
                cancelRewardAction(isApplied, isLoading);

            },
            /**
             * show input text on select change
             */
            showField: function (option, value) {
                maxValue(value.target.selectedOptions[0].getAttribute('data-reward'));
                if (typeof changeValue() != 'undefined' && changeValue() != '') {
                    $('#reward_points').attr('max',maxValue());
                    isVisible(true);
                } else {
                    isVisible(false);
                }
            },
            /**
             * Reward form validation
             *
             * @returns {boolean}
             */
            validate: function () {
                var form = '#reward-form';
                return $(form).validation() && $(form).validation('isValid');
            },
            setOptionAttr:function (option, value) {
                if (option.value != '') {
                    option.setAttribute('data-reward',value.remaining_reward_point);
                }
            },
        });
    }
);
