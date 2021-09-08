/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    'mage/translate',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, mageTemplate, alertBox) {
    'use strict';
    $.widget('mage.edit', {
        _create: function () {
            var self = this;
            var isLoaded = false;
            var products = $.parseJSON(self.options.products);
            var selectedProducts = [];
            if ($.isArray(products)) {
                $.each(products, function(index, value) {
                    var id = parseInt(value);
                    if (selectedProducts.indexOf(id) == -1) {
                        selectedProducts.push(id);
                    }
                });

                $(document).ajaxComplete(function (event, request, settings) {
                    var responseData = $.parseJSON(request.responseText);
                    var currentAjaxUrl = settings.url;
                    if (currentAjaxUrl.indexOf("mpsellercategory_category_product_list") && responseData.totalRecords > 0) {
                        $('.wk-mpsellercategory-container .wk-category-products .admin__data-grid-loading-mask').addClass("wk-display-block");
                        setTimeout(function () {
                            if ($('.wk-mpsellercategory-container .wk-category-products .data-row').length) {
                                $.each(selectedProducts, function(index, value) {
                                    if ($("#productIdscheck"+value).prop("checked") == false) {
                                        $("#productIdscheck"+value).trigger("click");
                                    }
                                });
                                mangeProducts();
                            }

                            $('.wk-mpsellercategory-container .wk-category-products .admin__data-grid-loading-mask').removeClass("wk-display-block");
                            if (!isLoaded) {
                                arrangeClasses();
                                isLoaded = true;
                            }
                        }, 2000);
                        setTimeout(function () {
                            manageLabels();
                        }, 2000);
                    }
                });
            }

            $(document).on("change", '.wk-mpsellercategory-container .wk-category-products input:checkbox', function() {
                mangeProducts();
            });

            $(document).on("click", '.wk-mpsellercategory-container .wk-category-products .action-menu li', function() {
                mangeProducts();
            });

            $(document).on("submit", '#form-save-category', function() {
                var ids = selectedProducts.join(",");
                $("#product_ids").val(ids);
            });

            function manageLabels()
            {
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
            }

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

            function mangeProducts()
            {
                setTimeout(function () {
                    $('.wk-mpsellercategory-container .wk-category-products tbody input:checkbox').each(function() {
                        var id = parseInt($(this).val());
                        if ($(this).prop("checked") == true) {
                            if (selectedProducts.indexOf(id) == -1) {
                                selectedProducts.push(id);
                            }
                        } else {
                            selectedProducts = $.grep(selectedProducts, function(value) {
                                return value != id;
                            });
                        }
                    });
                }, 10);
            }
        }
    });
    return $.mage.edit;
});
