define(['jquery'], function ($) {'use strict';

return function (config, element) {
    $(element).on('click', function (event) {
        event.preventDefault();
        var productId, input, currentQty, currentOperation;
         productId = $(element).data();
         input = $("input[data-product-item='" + productId.productItem + "']");
         currentQty  = input.val();
         currentOperation = this.className;
         if(currentOperation == 'increaseQty') {
             input.val( +currentQty + 1 );
         }else {
             var newQty = +currentQty - 1;
             if (newQty < 1)
             {
                 newQty = 1;
             }
             input.val(newQty);
         }
    });

}});
