/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

var config = {
    config: {
        mixins: {
        'Magento_Checkout/js/action/set-billing-address': {
            'Webkul_RegionUpload/js/action/set-billing-address-mixin': true
        },
        'Magento_Checkout/js/action/set-shipping-information': {
            'Webkul_RegionUpload/js/action/set-shipping-information-mixin': true
        },
        'Magento_Checkout/js/action/create-shipping-address': {
            'Webkul_RegionUpload/js/action/create-shipping-address-mixin': true
        },
        'Magento_Checkout/js/action/place-order': {
            'Webkul_RegionUpload/js/action/set-billing-address-mixin': true
        },
        'Magento_Checkout/js/action/create-billing-address': {
            'Webkul_RegionUpload/js/action/set-billing-address-mixin': true
        }
    }
}
};
