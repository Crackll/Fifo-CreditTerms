define(
    [
        'Webkul_MpRewardSystem/js/view/checkout/summary/rewardamount',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, defaultTotal, totals) {
        'use strict';

        return Component.extend({

            /**
             * Reward application procedure
             */
            initialize: function () {
                this._super();
                defaultTotal.estimateTotals();
            },

            /**
             * @override
             */
            isDisplayed: function () {
                var price = 0;
                if (this.totals()) {
                  if (totals.getSegment('rewardamount') !== null) {
                    price = totals.getSegment('rewardamount').value;
                  }
                }
                price = parseFloat(price);
                if (price) {
                    return true;
                } else {
                    return false;
                }
            }
        });
    }
);
