/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */ 

define([
    'uiComponent',
    'Magento_Customer/js/model/address-list',
    'mage/translate',
    'Magento_Customer/js/model/customer'
], function (Component, addressList, $t, customer) {
    var mixin = {
        initConfig: function () {
            this._super();
            if (window.checkoutConfig.wkForcedMainAddress == 1) {
                this.addressOptions.pop()
                this.addressOptions.push({
                    getAddressInline: function () {
                        return "";
                    },
                    customerAddressId: null
                });
            }
            return this;
        }
    };

    return function (target) { 
        return target.extend(mixin);
    };
});
