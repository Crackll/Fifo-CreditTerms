/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    "jquery/ui"
], function ($, $t, mageTemplate, alert, modal) {
    'use strict';
    $.widget('mage.wholesalerLists', {
        options: {
            opacityLow: '0.4',
            opacityHigh: '1',
            ajaxSuccessMessage: $t('Your quotation has been sent successfully.'),
            ajaxErrorMessage: $t('There was error during fetching results.'),
            quoteErrorMessage: $t('Unable to save quote, Please try again.'),
            quoteLimitMsg: $t('Quote quantity limit is ')
        },
        _create: function () {
            var self = this;
            $('body').delegate(
                self.options.wkPriceListButton,
                'click',
                function (e) {
                    e.preventDefault();
                    var wholesalerProductId = $(this).attr('data-wholesaler-product-id');
                    self.callAjaxFunction(wholesalerProductId);
                }
            );
            $('body').delegate(
                self.options.wkQuoteButton,
                'click',
                function (e) {
                    $(document).find('#wk_quotation_product_id').val($(this).attr('data-product-id'));
                    $(document).find('#wk_quotation_wholesale_product_id').val($(this).attr('data-wholesaler-product-id'));
                    $(document).find('#wk_quotation_wholesaler_id').val($(this).attr('data-wholesaler-id'));
                    $(document).find('.wk-qs-min-qty').text(self.options.quoteLimitMsg+$(this).attr('data-qty-limit'));
                    $(document).find('#quote_qty').addClass('digits-range-'+$(this).attr('data-qty-limit'));
                    $(document).find('.wk-mp-model-popup').addClass('_show');
                    $(self.options.popoverbackgroundhtml).show();
                }
            );
            $(self.options.popOverclose).on("click",function () {
                $(self.options.reset).trigger('click');
                $(self.options.popoverbackgroundhtml).hide();
            });
            $(self.options.submitButton).on('click', function (e) {
                e.preventDefault();
                var formData = $(self.options.quotationForm).serialize();
                self.saveQuatationFunction(formData);
            });
        },
        saveQuatationFunction: function (formData) {
            var self = this;
            if ($('#quotation-form').valid()) {
                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: self.options.quatationSaveUrl,
                    data: formData,
                    success: function (response) {
                        if (response == 0) {
                            alert({
                                content: self.options.quoteErrorMessage,
                                actions: {
                                    always: function () {
                                        $(self.options.popOverclose).trigger('click');
                                      
                                    }
                                }
                            });
                        } else {
                            $(self.options.popOverclose).trigger('click');
                            alert({
                                content: self.options.ajaxSuccessMessage,
                                actions: {
                                    always: function () {
                                        $(self.options.popOverclose).trigger('click');
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    },
                    error: function (response) {
                        alert({
                            content: self.options.ajaxErrorMessage,
                            actions: {
                                always: function () {
                                    $(self.options.popOverclose).trigger('click');
                                }
                            }
                        });
                    }
                });
            }
        },
        callAjaxFunction: function (wholesalerProductId) {
            var self = this;
            if (wholesalerProductId) {
                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: self.options.ajaxUrl,
                    data: {
                        wholesaler_product_id: wholesalerProductId
                    },
                    success: function (response) {
                        $(document).find('.wk_ssp_header_table > tbody').html('');
                        var progressTmpl = mageTemplate(self.options.pricelistTemplate),
                                        tmpl;
                        for (var i=0; i<response.length; i++) {
                            tmpl = progressTmpl({
                                data: {
                                    price: response[i].price,
                                    unit: response[i].unit,
                                    qty: response[i].qty,
                                    text: response[i].text,
                                }
                            });
                            $(document).find('.wk_ssp_header_table > tbody').append(tmpl);
                            $('body').append($(self.options.mpAttentionData));
                            $(self.options.pageWrapperSelector).css('opacity', self.options.opacityLow);
                            $(self.options.mpAttentionPopupModel).addClass(self.options.showClass);
                            $(self.options.mpAttentionData).show();
                            $(self.options.wkAttentionClose).click(function () {
                                console.log("hello");
                                $('body').removeClass('_has-modal');
                                $('.modals-overlay').remove();
                                $(self.options.pageWrapperSelector).css('opacity', self.options.opacityHigh);
                                $(self.options.mpAttentionData).hide();
                            });
                        }
                    },
                    error: function (response) {
                        alert({
                            content: $t('There was error during getting price list')
                        });
                    }
                });
            }
        }
    });
    return $.mage.wholesalerLists;
});
