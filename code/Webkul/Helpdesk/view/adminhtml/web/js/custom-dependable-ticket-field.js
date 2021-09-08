/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
        "jquery",
        'mage/translate',
        "mage/template",
        "mage/mage",
        "mage/calendar",
    ], function ($, $t,mageTemplate, alert) {
        'use strict';
        $.widget('mage.customDependableTicketField', {

            options: {
                optionTemp : '',
                allowedExtensionTmp : '#allowed_extension_template',
                tabMainContent : '#page_tabs_main_content',
                customFiledOption : '.customattribute_options',
                baseFieldSelector : '#ts_base_fieldset',
                frontEndClass : '.field-frontend_class',
                isRequired : '.field-is_required',
                frontEndInput: '#ts_frontend_input',
            },
            _create: function () {
                var self = this;
                this.options.optionTemp = $(self.options.tabMainContent).find(self.options.customFiledOption);
                $(self.options.tabMainContent).find(self.options.customFiledOption).hide();

                if (self.options.codeSignal == 1) {
                    $(self.options.baseFieldSelector).find("#ts_attribute_code").attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.frontEndInput).attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(self.options.baseFieldSelector).find(self.options.isRequired).show();
                }

                if (self.options.fileSignal == "1") {
                    $(self.options.baseFieldSelector).find(self.options.frontEndClass).hide();
                    $(self.options.baseFieldSelector).find(self.options.isRequired).show();
                    var progressTmpl = mageTemplate(self.options.allowedExtensionTmp),
                                  tmpl;
                        tmpl = progressTmpl({
                                data: {
                                    allowedextension: self.options.fileExtensionValue
                                }
                            });
                        $('.field-frontend_input').after(tmpl);
                }
                if (self.options.selectSignal == "1") {
                    $(self.options.tabMainContent).find(self.options.customFiledOption).show();
                    $(self.options.baseFieldSelector).find("#ts_frontend_class").attr('disabled','true');
                }
                if (self.options.textareaSignal == "1") {
                    $(self.options.baseFieldSelector).find("#ts_frontend_class").attr('disabled','true');
                }
                if (self.options.booleanSignal == "1") {
                    $(self.options.baseFieldSelector).find("#ts_frontend_class").attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.isRequired).hide();
                }
                $(self.options.frontEndInput).on('change',function () {
                    self._manageFields($(this));
                });
            },
            _manageFields: function (thisval) {
                var self = this;

                $(thisval).parents(self.options.baseFieldSelector).find(".selectoption_type_container").remove();
                self.options.optionTemp.remove();
                $(thisval).parents(self.options.tabMainContent).find(".allowed_extensions_type_container").remove();
                if ($(thisval).val() == 'text') {
                    $(self.options.tabMainContent).find(self.options.customFiledOption).hide();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#ts_frontend_class").removeAttr('disabled');
                }
                if ($(thisval).val() == 'textarea') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#ts_frontend_class").attr('disabled','true');
                }
                if ($(thisval).val() == 'boolean') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").hide();
                    $(thisval).parents(self.options.baseFieldSelector).find("#ts_frontend_class").attr('disabled','true');
                }

                if ($(thisval).val() == 'date') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#ts_frontend_class").attr('disabled','true');
                }

                if ($(thisval).val() == 'select' || $(thisval).val() == 'multiselect') {
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#ts_frontend_class").attr('disabled','true');
                    $(self.options.tabMainContent).find("#ts_base_fieldset").append(self.options.optionTemp);
                    self.options.optionTemp.show();
                }

                if ($(thisval).val() == 'file' || $(thisval).val() == 'image') {
                    var extn = 'jpg,png,gif';
                    if ($(thisval).val() == 'file') {
                        extn = 'pdf,zip,doc';
                    }
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).hide();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(self.options.tabMainContent).find(self.options.customFiledOption).hide();
                    var progressTmpl = mageTemplate(self.options.allowedExtensionTmp),
                              tmpl;
                    tmpl = progressTmpl({
                            data: {allowedextension: extn}
                        });
                    $('.field-frontend_input').after(tmpl);
                }
            }
        });
    return $.mage.customDependableTicketField;
    });
