require([
    "jquery",
    'mage/translate',
], function ($, $t) {
    $(function () {
        window.wkdailydealLoaded = false;
        var proIds =[];
        var dataloaded = false;
        var updatedPro = [];
        var url=window.Mpdailyurl;
        // $.ajax({
        //     type: "POST",
        //     url: url,
        //     dataType: "json",
        //     cache: false,
        //     success: function (response) {
        //         if (response.success) {
        //             dataloaded = true;
        //             var data = response.data;
        //             $.each($('.deal.wk-daily-deal'), function (ind, val) {
        //                 var dealId = $(val).data('deal-id');
        //                 if (dealId != undefined && dealId) {
        //                     if (data[dealId]!=undefined) {
        //                         updatedPro.push(dealId);
        //                         updatedPro = updatedPro.concat(data[dealId]['parent']);
        //                         var countClock = $(val).find('.wk_cat_count_clock');
        //                         $(countClock).attr('data-stoptime', data[dealId]['stoptime']);
        //                         $(countClock).attr('data-diff-timestamp', data[dealId]['diff_timestamp']);
        //                     } else {
        //                         dataloaded = false;
        //                     }
        //                 }
        //             });
        //             $.each($('div.price-box'), function (ind1, val1) {
        //                 var productId = $(val1).data('product-id');
        //                 proIds.push(productId);
        //                 if (productId != undefined && productId) {
        //                     if (data[productId]!=undefined && !(updatedPro.includes(productId) || updatedPro.includes(productId+''))) {
                                
        //                         dataloaded = false;
        //                     }
        //                 }
        //             });

        //             if (dataloaded) {
        //                 $(document).ready(function () {
        //                     $('body').trigger('wkdailydealLoaded');
        //                     window.wkdailydealLoaded = true;
        //                 });
        //             } else {
        //                 $(document).ready(function () {
        //                     $('body').trigger('wkdailydealLoaded');
        //                     window.wkdailydealLoaded = true;
        //                 });
                        
        //                 $.ajax({
        //                     type: "post",
        //                     url: window.BASE_URL+"mpdailydeal/index/cacheflush",
        //                     dataType: "json",
        //                     data:{ids:proIds.toString()},
        //                     cache: false,
        //                     success: function (response) {
        //                     }
        //                 });
        //             }
        //         }
        //     },
        //     error: function (response) {
        //     }
        // });
    });
});
