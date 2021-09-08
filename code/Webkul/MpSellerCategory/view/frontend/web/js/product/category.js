/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require([
    'jquery',
    "Magento_Ui/js/modal/alert"
], function($, alertBox) {
    $(document).ready(function() {
        $("#name").parent().parent().before($('#seller-category-container'));
        $('#seller-category-container').removeClass("wk-display-none");
    });
});
