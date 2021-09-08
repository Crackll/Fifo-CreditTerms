/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
 define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/adminhtml/grid',
    "jquery/ui"
], function ($, $t, alert, confirm) {
    'use strict';
    $.widget('mage.WkMpRewardsystem', {
        options: {
            backUrl: '',
            confirmMessageToAddRewardProduct: $t('Are you sure you want to set reward points on selected product?'),
            alertMessageProduct: $.mage.__('Please Select Products To Assign Reward Points'),
            confirmMessageToAddRewardCategory: $t('Are you sure you want to set reward points on selected category?'),
            alertMessageCategory: $.mage.__('Please Select Categories To Assign Reward Points')
        },
        _create: function () {
            var self = this;

            var dataForm = $(self.options.massupdateform);
            dataForm.mage('validation', {});
            var dataFormcategory = $(self.options.massupdateformcategory);
            dataFormcategory.mage('validation', {});
            $('#rewardproductgrid_massaction-select').hide();
            $('#rewardcategorygrid_massaction-select').hide();

            $(self.options.savebtn).on('click', function (e) {
                if ($(self.options.massupdateform).valid()!=false) {
                    var categoryObject = $.parseJSON($('#wkproductids').attr("value"));
                    var length = Object.keys(categoryObject).length;
                    if (length <= 0) {
                        alert({
                            content: self.options.alertMessageProduct
                        });
                    } else {
                    var dicision = confirm({
                        content: self.options.confirmMessageToAddRewardProduct,
                            actions: {
                                confirm: function () {
                                    $(self.options.massupdateform).submit();
                                    $(self.options.savebtn).text($t("Saving")+'..');
                                    $(self.options.savebtn).css('opacity','0.7');
                                    $(self.options.savebtn).css('cursor','default');
                                    $(self.options.savebtn).attr('disabled','disabled');
                                }
                            }
                        });

                    }
                    
                }
            });
            $(self.options.categorySavebtn).on('click', function (e) {
                if ($(self.options.massupdateformcategory).valid()!=false) {
                    var categoryObject = $.parseJSON($('#in_reward_category').attr("value"));
                    var length = Object.keys(categoryObject).length;
                    if (length <= 0) {
                        alert({
                            content: self.options.alertMessageCategory
                        });
                    }else{
                        var dicisionCategory = confirm({
                        content: self.options.confirmMessageToAddRewardCategory,
                        actions: {
                            confirm: function () {                                   
                                    $(self.options.massupdateformcategory).submit();
                                    $(self.options.savebtn).text($t("Saving")+'..');
                                    $(self.options.savebtn).css('opacity','0.7');
                                    $(self.options.savebtn).css('cursor','default');
                                    $(self.options.savebtn).attr('disabled','disabled');
                                }
                            },
                        });
                    }                    
                }
            });
        }
    });
    return $.mage.WkMpRewardsystem;
});

