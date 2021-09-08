/**
 * MpRewardSystem
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    "Magento_Ui/js/modal/alert",
    "Magento_Ui/js/modal/confirm",
    "jquery/ui",
    "mage/calendar"
], function ($, $t, alert, confirm) {
    'use strict';
    $.widget('mage.mprewardcartrule', {
      options: {
            deleteData: $t("Are you sure, you want to delete?"),
            deleteAllData: $t("Are you sure, you want to delete these Cart rules?"),
            editData: $t("Are you sure, you want to edit?")
        },
        _create: function () {
            var self = this;
            var addNewCartRule = $(self.options.formvalidate);
            addNewCartRule.mage('validation', {});
            $('body').find("#form-validate").dateRange({
                    'dateFormat':'yyyy/mm/dd',
                    'from': {
                        'id': 'start_date'
                    },
                    'to': {
                        'id': 'end_date'
                    }
            });
            $('body').find("#form-cartrulelist-filter").dateRange({
                'dateFormat':'yyyy/mm/dd',
                'from': {
                    'id': 'special-from-date'
                },
                'to': {
                    'id': 'special-to-date'
                }
            });
            $('.mprewardCart_edit').on('click',function () {
                var id = $(this).parents('.wk_row_list').find('.hidden-id').val();
                $('body').find("#editRule").dateRange({
                    'dateFormat':'yyyy-mm-dd',
                    'from': {
                        'id': 'startdate'
                    },
                    'to': {
                        'id': 'enddate'
                    }
                });
                var JSONArray = $.parseJSON($(this).parents('.wk_row_list').find('.data').val());
                var dicision = confirm({
                    content: self.options.editData,
                    actions: {
                        confirm: function () {
                            $.each(JSONArray,function (key,value) {
                                $('#amountfrom').attr('value',JSONArray.amount_from);
                                $('#amountto').attr('value',JSONArray.amount_to);
                                $('#reward_points').attr('value',JSONArray.points);
                                $('#startdate').attr('value',JSONArray.start_date);
                                $('#enddate').attr('value',JSONArray.end_date);
                                $('#reward_status').attr('value',JSONArray.status);
                                $('#editRule').attr('action',self.options.editUrl+"entity_id/"+id);
                                $('.top-container ').css('z-index','-10');
                                $('.wk_cart_rule_wrapper').show();
                            });
                        },
                    }
                });
            });
            $(self.options.deleteSetRate).on('click',function () {
                var id = $(this).parents('.wk_row_list').find('.hidden-id').val();
                var dicision = confirm({
                    content: self.options.deleteData,
                    actions: {
                        confirm: function () {
                            window.location=self.options.deleteUrl+"id/"+id;
                        },
                    }
                });
            });
            $(self.options.wkCloseWrap).on('click',function () {
                  $(self.options.wkRuleWrap).hide();
                  $('.top-container').css('z-index','10');
            });
            $('#mpselecctall').click(function (event) {
                if (this.checked) {
                    $('.mpcheckbox').each(function () {
                        this.checked = true;
                    });
                } else {
                    $('.mpcheckbox').each(function () {
                        this.checked = false;
                    });
                }
            });
            $('#mass-delete-butn').click(function (e) {
                var flag =0;
                e.preventDefault();
                $('.mpcheckbox').each(function () {
                    if (this.checked === true) {
                        flag =1;
                    }
                });
                if (flag === 0) {
                    alert({content : $t(' No Checkbox is checked ')});
                    return false;
                } else {
                    var dicision = confirm({
                        content: self.options.deleteAllData,
                        actions: {
                            confirm: function () {
                                $('#form-cartrulelist-massdelete').submit();
                            },
                        }
                    });
                }
            });
        }
    });
    return $.mage.mprewardcartrule;
});
