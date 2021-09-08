/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiCollection',
    'jquery',
    'ko',
    'uiLayout',
    'mageUtils',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'uiRegistry'
], function (Collection, $, ko, layout, utils, _, $t, modal, uiRegistry) {
    'use strict';

    return Collection.extend({
        defaults: {
            attribute: {},
            template: 'Webkul_MpMSI/container',
            identifier: 'source_code',
            dataScope: '',
            currentAttribute: {},
            insertListingComponent: '',
            dynamicRowsName: 'dynamicRows',
            type: '',
            templateElementNames: {
                button: 'button_template',
                dynamicRows: 'dynamic_rows_template',
                group: 'group_template'
            },
            ignoreTmpls: {
                childTemplate: true
            },
            attribute: ko.observable({}),
            type: ko.observable('none'),
            insertListingValue: {},
            insertListing: {},
            savedSourceCodes: [],
            rowIndex: 0,
            modalClass: "wk-modal-configurable-item",
            currentSourceStocks: [],
            gridPopupComponent:'Webkul_MpMSI/js/grid-popup',
            gridPopupTitle: 'Assign Sources',
            inputType: true,
            currentChildGrid: {},
            sourcesIds: ko.observable(""),
            source: '',
            buttonTitle: "Assign Sources",
            labelVisible: ko.observable(''),
            savedSources: ko.observable({}),
            dynamicRowsCollection: {}

        },

        initialize: function () {
            this._super();
            this.source = uiRegistry;
            this.attribute.subscribe(function (data) {
                this.handlerAttributeChange(data);
            }.bind(this));

            this.type.subscribe(function (data) {
                this.handlerTypeChange(data);
            }.bind(this)); 

            this.sourcesIds.subscribe(function (data) {
                // console.log(data);
            });


            if (this.currentSourceStocks.length > 0) {
                this.savedSourceCodes = this.currentSourceStocks.map(item => item.source_code);
            }
            window.qr =this;
            return this;
        },

        /**
         * Generates data for dynamic-rows records
         * @param {Array} data
         *
         * @returns {Array}
         */
        generateDynamicRowsData: function (data) {
            var items = [];

            _.each(data, function (item) {
                items.push({
                    'source': item.name,
                    'source_code': item[this.identifier],
                    'source_status': parseInt(item.enabled, 10) ? $.mage.__('Enabled') : $.mage.__('Disabled')
                });
            }.bind(this));

            return items;
        },

        /**
         * Handler for InsertListing value
         *
         * @param {Array} data
         */
        handlerInsertValueChange: function (data) {
            var items,
                path = this.dynamicRowsName + '.' + this.currentDynamicRows;
            if (!this.currentDynamicRows) {
                return;
            }
            if (!data.length) {
                return;
            }
        },

        /**
         * Handler for attribute property
         *
         * @param {Object} data
         */
        handlerAttributeChange: function (data) {
            if (data && data !== this.currentAttribute) {
                this.currentAttribute = data;
                
                _.each(data.chosen, function (item) {
                    this.destroyChildren();
                    this.addChild(item);
                    this.labelVisible(item.label);
                }.bind(this));
            }
        },

        /**
         * Handler for type property
         *
         * @param {String} data
         */
        handlerTypeChange: function (data) {
            if (data === 'single') {
                this.currentAttribute = {};
                this.destroyChildren();
                this.addChild();
                this.labelVisible("Skip All");
            } else if (data === 'each' && this.attribute) {
                this.handlerAttributeChange(this.attribute);
            }
        },

        /** @inheritdoc */
        validate: function (elem) {
            if (typeof elem === 'undefined') {
                return;
            }

            if (typeof elem.validate === 'function') {
                this.valid = this.valid & elem.validate().valid;
            } else if (elem.elems) {
                elem.elems().forEach(this.validate, this);
            }
        },

        _isElementLoaded: function ($element, callback) {
            var self = this;
            setTimeout(function() {
                
                if ($($element).is(':visible')) {
                    callback();
                } else {
                    self._isElementLoaded($element, callback);
                }
            }, 50);
        },

        /**
         * @param {Object | Undefined} data - optional.
         */
        addChild: function (data) {
            
            if (!data) {
                data = {
                    attribute_code: "skip",
                    label: "Skip All"
                }
            }
            let sourceIds = this.savedSources()[data.attribute_code+'_'+data.label.replace(/ /g, '')];
    
            let dynamicRowButton = {
                parent: this.name,
                name: `${this.dynamicRowsName}.button.${data.attribute_code}.${data.label}`,
                component: "Webkul_MpMSI/js/assign-source-config-button",
                template: "Webkul_MpMSI/assign-config-sources.html",
                provider:this,
                childComponent: `${this.name}.${this.dynamicRowsName}.${data.attribute_code}.${data.label}`,
                config: {
                    labelVisible: data.label
                }
            };
            let dynamicRow = {
                parent: this.name,
                name: `${this.dynamicRowsName}.${data.attribute_code}.${data.label}`,
                component: "Webkul_MpMSI/js/assign-source-configurable",
                template: "Webkul_MpMSI/sources-form.html",
                displayArea:'component-sources-form',
                provider: `${this.name}.${this.dynamicRowsName}.button.${data.attribute_code}.${data.label}`,
                config: {
                    attributeCode: data.attribute_code,
                    attributeValue: data.label,
                    sourcesIds: sourceIds
                }
            };
            layout([dynamicRowButton, dynamicRow]);
        }
    });
});
