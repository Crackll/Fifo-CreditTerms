/**
 * Webkul_MpDailyDeal DailyDeals.setAttribute
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

 /*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('dailydeals.setattr', {
        _create: function () {
            var alreadyFilled = false;
            var attribute = this.options;
            var length = $('input[name="product[deal_from_date]"]').length;
            if (attribute.moduleEnable == 0 || attribute.productType == 'grouped' || attribute.productType == 'etickets' || attribute.productType == 'booking'||attribute.productType == 'configurable') {
                $('div[data-index="daily-deals"]').hide();
            }
            $('body').on('click', '[data-index=daily-deals]', function (event) {
                if (!alreadyFilled) {
                    setTimeout(function () {
                        alreadyFilled = true;
                        $('input[name="product[deal_to_date_tmp]"]').attr('value', attribute.dealTo).css('width','200px');
                        $('input[name="product[deal_from_date_tmp]"]').attr('value', attribute.dealFrom).css('width','200px');
                        if ($('select[name="product[deal_status]"]').val() == 0) {
                            $('input[name="product[deal_to_date_tmp]"]').attr('disabled', 'disabled');
                            $('input[name="product[deal_from_date_tmp]"]').attr('disabled', 'disabled');
                            $('input[name="product[deal_value]"]').attr('disabled', 'disabled');
                            $('select[name="product[deal_discount_type]"]').attr('disabled', 'disabled');
                        }
                    }, 500);
                }
                if ($('select[name="product[deal_status]"]').val() == 0) {
                    $('input[name="product[deal_to_date_tmp]"]').attr('disabled', 'disabled');
                    $('input[name="product[deal_from_date_tmp]"]').attr('disabled', 'disabled');
                    $('input[name="product[deal_value]"]').attr('disabled', 'disabled');
                    $('select[name="product[deal_discount_type]"]').attr('disabled', 'disabled');
                }
                });
                $('body').on('change', 'select[name="product[deal_status]"]', function (event) {
                    if ($(this).val() == 1) {
                        $('input[name="product[deal_to_date_tmp]"]').removeAttr('disabled');
                        $('input[name="product[deal_from_date_tmp]"]').removeAttr('disabled');
                        $('input[name="product[deal_value]"]').removeAttr('disabled');
                        $('select[name="product[deal_discount_type]"]').removeAttr('disabled');
                    } else {
                        $('input[name="product[deal_to_date_tmp]"]').attr('disabled', 'disabled');
                        $('input[name="product[deal_from_date_tmp]"]').attr('disabled', 'disabled');
                        $('input[name="product[deal_value]"]').attr('disabled', 'disabled');
                        $('select[name="product[deal_discount_type]"]').attr('disabled', 'disabled');
                    }
            });
            if (length > 0) {
                $('input[name="product[deal_discount_percentage]"]').parents('.admin__field').hide();
                $('input[name="product[deal_from_date]"]').parents('.admin__field').hide();
                $('input[name="product[deal_to_date]"]').parents('.admin__field').hide();

                $('input[name="product[deal_to_date_tmp]"]').attr('value', attribute.dealTo).css('width','200px');
                $('input[name="product[deal_from_date_tmp]"]').attr('value', attribute.dealFrom).css('width','200px');

                $('.ui-datepicker-trigger').click(function () {
                    $(this).prev('input').focus();
                });
            } else {
                $('body').on('click', 'div[data-index="daily-deals"]', function (event) {
                    $('input[name="product[deal_discount_percentage]"]').parents('.admin__field').hide();
                    $('input[name="product[deal_from_date]"]').parents('.admin__field').hide();
                    $('input[name="product[deal_to_date]"]').parents('.admin__field').hide();

                    $('input[name="product[deal_to_date_tmp]"]').attr('value', attribute.dealTo).css('width','200px');
                    $('input[name="product[deal_from_date_tmp]"]').attr('value', attribute.dealFrom).css('width','200px');
                    $('.ui-datepicker-trigger').click(function () {
                        $(this).prev('input').focus();
                    });
                    $(this).off(event);
                });
            }
        }
    });
    return $.dailydeals.setattr;
});