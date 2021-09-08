/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/model/customer',
    'magnificpopup'
], function ($, Component, customer) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return Component.extend({
        isGuestCheckoutAllowed: checkoutConfig.isGuestCheckoutAllowed,
        isCustomerLoginRequired: checkoutConfig.isCustomerLoginRequired,
        registerUrl: checkoutConfig.registerUrl,
        forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
        autocomplete: checkoutConfig.autocomplete,
        defaults: {
            template: 'MageBig_SocialLogin/authentication'
        },

        /**
         * Is login form enabled for current customer.
         *
         * @return {Boolean}
         */
        isActive: function () {
            return !customer.isLoggedIn();
        },

        initLoginPopup: function () {
            $('.action-auth-toggle').magnificPopup({
                type: 'inline',
                removalDelay: 300,
                mainClass: 'mfp-move-from-top',
                closeOnBgClick: false,
                callbacks: {
                    open: function() {
                        if( this.fixedContentPos ) {
                            if(this._hasScrollBar(this.wH)){
                                var s = this._getScrollbarSize();
                                if(s) {
                                    $('.sticky-menu.active').css('padding-right', s);
                                    $('#go-top').css('margin-right', s);
                                }
                            }
                        }
                    },
                    close: function() {
                        $('.sticky-menu.active').css('padding-right', '');
                        $('#go-top').css('margin-right', '');
                    }
                },
                midClick: true
            });
        }
    });
});
