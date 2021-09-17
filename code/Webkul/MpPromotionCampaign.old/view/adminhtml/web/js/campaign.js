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
    $( document ).ready(function() {
        var myVar =   setInterval(function () {
            if($("._has-datepicker").length){
                clearTimeout(myVar);
                $("._has-datepicker").focusout(function(){  
                    var data = $(this).val();  
                    if (!parseInt(Date.parse(data))) {  
                        $(this).val('');
                    }
                    
                });
            }
        }, 100);
    });
})
    