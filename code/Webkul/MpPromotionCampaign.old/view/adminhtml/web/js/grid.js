/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

require([
    "jquery"
    
], function ($) {
    var url = window.location.href;
    var data = url.split("id");
    var id = data[1].split('/');
    var count = 0;
    setInterval(function () {
        if (count == 0) {
            if ($(".page-actions-buttons .primary").length > 0) {
                count = 1;
                var clickData = $(".page-actions-buttons .primary").attr("onclick");
                var referData =  clickData.split('addproduct');
                str2 = 'addproduct/id/'+id[1];
               var str =  referData[0].concat(str2);
               var referUrl = str.concat(referData[1]);
               $(".page-actions-buttons .primary").attr("onclick",referUrl);
            }
            return false;
        }
    }
 , 1000);
})

    