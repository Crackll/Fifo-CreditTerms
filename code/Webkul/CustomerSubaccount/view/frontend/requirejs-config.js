/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

var config = {
    map: {
        '*': {
            subaccountlist: 'Webkul_CustomerSubaccount/js/subaccountlist',
            approvecartlist: 'Webkul_CustomerSubaccount/js/approvecartlist',
            mergecartlist: 'Webkul_CustomerSubaccount/js/mergecartlist',
            mycartlist: 'Webkul_CustomerSubaccount/js/mycartlist',
            cartupdate: 'Webkul_CustomerSubaccount/js/cartupdate',
            reviewProduct: 'Webkul_CustomerSubaccount/js/reviewProduct',
            subaccountedit: 'Webkul_CustomerSubaccount/js/subaccountedit',
            multishippingcheckout: 'Webkul_CustomerSubaccount/js/multishippingcheckout',
        }
    },
    config:
       {
            mixins: {
                'Magento_Checkout/js/view/shipping': {
                    'Webkul_CustomerSubaccount/js/view/shipping': true
                },
                'Magento_Checkout/js/view/billing-address/list': {
                    'Webkul_CustomerSubaccount/js/view/billing-address/list': true
                },
            }
       }
};
