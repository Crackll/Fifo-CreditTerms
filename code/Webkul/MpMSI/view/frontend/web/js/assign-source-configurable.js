define([
    'uiComponent',
    'uiLayout',
    'uiRegistry',
    'mage/translate',
    'mage/template',
    'jquery',
    'ko',
    'Magento_Ui/js/modal/modal'
], function(
    Component,
    layout,
    registry,
    $t,
    mageTemplate,
    $,
    ko,
    modal
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: "Webkul_MpMSI/sources-form.html",
            inputType: false,
            gridPopupTitle: "Items",
            currentSourceStocks: {},
            savedSourceCodes: [],
            rowIndex: 0,
            sources: [],
            attributeCode: '',
            attributeValue: '',
            sourcesIds: [],
            existingSource: {}
        },

        /**
         * @extends
         */
        initialize: function () {
            var self = this;
            this._super();
            this.existingSource.items = window.sources.items.filter(function (item) { return true;});
            if (registry.get(this.provider) != undefined) {
                registry.get(this.provider).sourcesIds.subscribe(function (ids) {
                    let sids = this.sourcesIds();
                    ids = [...new Set(sids.concat(ids.split(",")))];
                    this.sourcesIds(ids);
                    
                    registry.get(this.parent).savedSources()[this.attributeCode()+'_'+this.attributeValue().replace(/ /g, '')] = this.sourcesIds();
                    this.addRow();
        
                }.bind(this));
    
                // this.addRow();
            }
        },

        /**
         * initialize observers
         */
        initObservable: function () {
            this._super().observe('inputType sourcesIds sources attributeCode attributeValue');
            return this;
        },

        getFieldName: function (code, value, index, name) {
            if (code !== 'skip') {
                return `quantity_resolver[${code}][${value}][dynamicRows][${index}][${name}]`;
            } else {
                return `quantity_resolver[dynamicRows][dynamicRows][${index}][${name}]`;
            }
        },
        
        addRow: function () {
            let self = this;
            let sourcesIds = this.sourcesIds();
            if (sourcesIds.length) {
                let selectedSources = this.existingSource.items.map((item) => {
                    if (sourcesIds.indexOf(item.source_code) !== -1) {
                        item.key = item.source_code;
                        item.quantity = ko.observable();
                        if (item.enabled) {
                            item.enabled = $t("Enable");
                        } else {
                            item.enabled = $t("Disable");
                        }
                        return item;
                    } else {
                        return false;
                    }
                });
                
                selectedSources = selectedSources.filter((item) => {
                    if (item) {
                        return true;
                    } else {
                        return false;
                    }
                });
                this.sources(selectedSources);

                let dynamicRows = registry.get(this.parent).dynamicRowsCollection;
                let item = {};
                if (this.attributeCode() !== "skip") {
                    if (!dynamicRows.hasOwnProperty(this.attributeCode())) {
                        dynamicRows[this.attributeCode()] = {};
                        dynamicRows[this.attributeCode()][this.attributeValue()] = selectedSources;
                    } else {
                        if (!dynamicRows[this.attributeCode()].hasOwnProperty(this.attributeValue())) {
                            dynamicRows[this.attributeCode()][this.attributeValue()] = selectedSources;
                        } else {
                            dynamicRows[this.attributeCode()][this.attributeValue()] = selectedSources;
                        }
                    }   
                } else {
                    dynamicRows[registry.get(this.parent).dynamicRowsName] = selectedSources;
                }

                var configure = $('.admin__data-grid-wrap.admin__data-grid-wrap-static');

                if (configure.length >= 2) {
                    $('.msi-fieldset').remove();
                    $('#msi-container').remove();
                }


                registry.get(this.parent).dynamicRowsCollection = dynamicRows;
            }
      
        },

        removeItem: function (data, event) {
            let provider = registry.get(this.provider);
            let ids = provider.sourcesIds().split(",");
            ids = ids.filter(function (id) {
                return id != data.source_code;
            });
            provider.sourcesIds(ids.join(","));
        },
        
        addEvents: function() {
            let that = this;
            $(".action-delete").off("click");
            $("body").on("click", ".action-delete", function() {
                let sourceCode = $(this).attr("data-source-code");
                let index = that.savedSourceCodes.indexOf(sourceCode);
                
                if (index > -1) {
                    that.savedSourceCodes.splice(index, 1);
                }
                $(this).parent().parent().find('input[class="admin__control-text required-entry validate-number"]').val('');
                $(this).parent().parent().remove();
            });
        },
    });
});
