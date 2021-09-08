/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/template',
    'uiComponent',
    'ko',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'mage/url',
    "jquery/ui",
    'mage/calendar'
], function ($, mageTemplate, Component, ko, $t, alert, urlBuilder) {
    'use strict';
    var totalSelected = ko.observable(0);
    return Component.extend({
        initialize: function () {
            this._super();
            var self = this;
            var checkBoxArray = {};
            var value = "";
            var selectStatus = "";
            var rewardPoint = "";

            var localStorageArray = localStorage.getItem("checkedData");
            if (localStorageArray != undefined || localStorageArray != null) {
                checkBoxArray = JSON.parse(localStorageArray);
                var dateCheck = new Date();
                var oldDate=new Date(checkBoxArray["date"]);
                var dateDiff = parseInt(dateCheck - oldDate)/1000;
                if (dateDiff > 120) {
                    localStorage.removeItem("checkedData");
                    alert({ content: $t('Not working on selected data from long time.') });
                } else {
                    $("#rewardpoint").val(checkBoxArray["rewardPoint"]);
                    $('#status').val(parseInt(checkBoxArray["status"]));
                    $('.mpcheckbox').each(function () {
                        var cboxValue = $(this).val();
                        var cboxChecked = checkBoxArray[cboxValue] == 'true' ? true : false;
                        if (cboxChecked) {
                            $(this).prop("checked", true);
                        }
                    });
                    $.each(checkBoxArray, function (key, value) {
                        if (value == "true") {
                            totalSelected(totalSelected() + 1);
                        }
                    });
                }
            }
            $('body').on('click', '.mpcheckbox', function (e) {
                if (this.checked === true) {
                    value = $(this).val();
                    rewardPoint = $("#rewardpoint").val();
                    selectStatus = $('#status option:selected').val();
                    var date = new Date();
                    checkBoxArray["date"] = date;
                    checkBoxArray["rewardPoint"] = rewardPoint;
                    checkBoxArray["status"] = selectStatus;
                    checkBoxArray[value] = "true";
                    localStorage.setItem("checkedData", JSON.stringify(checkBoxArray));
                }
                if (this.checked === false) {
                    value = $(this).val();
                    rewardPoint = $("#rewardpoint").val();
                    selectStatus = $('#status option:selected').val();
                    var date = new Date();
                    checkBoxArray["date"] = date;
                    checkBoxArray["rewardPoint"] = rewardPoint;
                    checkBoxArray["status"] = selectStatus;
                    checkBoxArray[value] = "false";
                    localStorage.setItem("checkedData", JSON.stringify(checkBoxArray));
                }
            });
            $('body').on('click', '#limiter', function (e) {
                localStorage.setItem("checkedData", JSON.stringify(checkBoxArray));
            });
            $('body').delegate('#form-categorylist-massassign', 'submit', function (e) {
                $("#checkedData").val(JSON.stringify(checkBoxArray));
                localStorage.removeItem("checkedData");
                var flag = 0;
                $('.mpcheckbox').each(function () {
                    if (this.checked === true) {
                        flag = 1;
                    }
                });
                if (flag === 0) {
                    alert({ content: $t('No Checkbox is checked ') });
                    return false;
                } else {
                    var dicisionapp = confirm($t("Are you sure you want to assign rewards to these category ? "));
                    if (dicisionapp === true) {
                        $('#form-customer-category-new').submit();
                    } else {
                        return false;
                    }
                }
            });

            $('body').delegate('.mpcheckbox', 'click', function (event) {
                var self = this;
                if (this.checked) {
                    totalSelected(totalSelected() + 1);
                } else {
                    totalSelected(totalSelected() - 1);
                }
            });

            $('body').delegate('#mpselecctall', 'click', function (event) {
                totalSelected(0);
                if (this.checked) {
                    $('.mpcheckbox').each(function () {
                        this.checked = true;
                        totalSelected(totalSelected() + 1);
                    });
                } else {
                    $('.mpcheckbox').each(function () {
                        this.checked = false;
                        totalSelected(0);
                    });
                }
            });
        },
        getTotalSelected: function () {
            return totalSelected();
        }
    });
});
