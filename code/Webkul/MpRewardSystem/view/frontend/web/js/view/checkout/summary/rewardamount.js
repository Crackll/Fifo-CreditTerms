/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Webkul_MpRewardSystem/checkout/cart/totals/rewardamount'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function () {
                var price = 0;
                if (this.totals()) {
                      price = totals.getSegment('rewardamount').value;
                }
                if (price) {
                    return true;
                } else {
                    return false;
                }
            },
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('rewardamount').value;
                }
                return this.getFormattedPrice(this.getBaseValue());
            },
            getBaseValue: function () {
                var price = 0;
                if (this.totals() && totals.getSegment('rewardamount').value) {
                    price = parseFloat(totals.getSegment('rewardamount').value);
                }
                return price;
            }
        });
    }
);
