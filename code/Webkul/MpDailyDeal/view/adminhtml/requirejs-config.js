/**
 * Webkul_MpDailyDeal requirejs config
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

var config = {
    map: {
        '*': {
            setattribute: 'Webkul_MpDailyDeal/js/setattribute',
            'Magento_ConfigurableProduct/js/components/price-configurable':'Webkul_MpDailyDeal/js/components/price-configurable'
        }
    }
};