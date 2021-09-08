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
        window.FORM_KEY = $("input[name=form_key]").val();
        $(document).ajaxComplete(function (event, request, settings) {
            var responseData = $.parseJSON(request.responseText);
            var currentAjaxUrl = settings.url;
            if (currentAjaxUrl.indexOf("mpsellercategory_category_list_front") && responseData.totalRecords > 0) {
                $('.wk-mpsellercategory-container .admin__data-grid-loading-mask').addClass("wk-display-block");
                $('.wk-mpsellercategory-container .admin__data-grid-wrap').addClass("wk-display-none");
                setTimeout(function () {
                    var labels = [];
                    var index = 1;
                    $(".wk-mpsellercategory-container thead th").each(function() {
                        var label = $(this).find(".data-grid-cell-content").text();
                        labels[index] = label;
                        index++;
                    });

                    $(".wk-mpsellercategory-container tbody tr").each(function() {
                        var index = 1;
                        $(this).find("td").each(function() {
                            if (labels[index] != "") {
                                $(this).attr("data-th", labels[index]);
                            }
                            index++;
                        });
                    });
                    arrangeClasses();
                    $('.wk-mpsellercategory-container .admin__data-grid-loading-mask').removeClass("wk-display-block");
                    $('.wk-mpsellercategory-container .admin__data-grid-wrap').removeClass("wk-display-none");
                }, 2000);
            }
        });
    });

    function arrangeClasses()
    {
        setTimeout(function () {
            var index = 1;
            $(".wk-mpsellercategory-container tbody tr:nth-child(1) td").each(function() {
                var className = $(this).attr("class");
                className = className+"-th";
                $(".wk-mpsellercategory-container thead tr th:nth-child("+index+")").addClass(className);
                index++;
            });
        }, 10);
    }
});
