/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Webkul_Marketplace/js/variations/steps/bulk',
    'jquery',
    'ko',
    'underscore'
], function (Bulk, $, ko, _) {
    'use strict';

    return Bulk.extend({
        defaults: {
            quantityModuleName: '',
            quantityPerSource: '',
            modules: {
                quantityResolver: '${$.quantityResolver}'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            var sections;
            this._super();
            sections = this.sections();
            sections.quantityPerSource = {
                label: 'Quantity Per Source',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            };
            this.sections(sections);
            
            let makeImages = this.makeImages;
            let qtySource = this.quantityPerSource;
            let price = this.price;
            /**
             * Make options sections.
             */
            this.makeOptionSections = function () {
                return {
                    images: new makeImages(null),
                    price: price,
                    quantityPerSource: qtySource
                };
            }.bind(this);

            this.sections().quantityPerSource.attribute.subscribe(function (data) {
                this.attribute(data);
                this.quantityResolver().attribute(data);
            }.bind(this));

            this.sections().quantityPerSource.type.subscribe(function (data) {
                //console.log(data);
                this.type(data);
                this.quantityResolver().type(data);
            }.bind(this));
            window.mythis = this;
            return this;
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'attribute',
                    'type'
                ]);

            return this;
        },

            /** @inheritdoc */
            force: function (wizard) {
                
                if (this.type() === 'each' && this.attribute() || this.type() === 'single') {
                    this.prepareDynamicRowsData();
                }
    
                this._super(wizard);
            },
    
            /**
             * Prepares dynamic rows data for the next step
             */
            prepareDynamicRowsData: function () {
              
                var data,
                    module = this.quantityResolver();
             
                if (this.type() === 'each') {
                    
                    data = module.dynamicRowsCollection[this.attribute().code];
                    _.each(this.attribute().chosen, function (item) {
                        item.sections().quantityPerSource = data[item.label];
                    });
                   
                } else if (this.type() === 'single') {
                    data = module.dynamicRowsCollection[module.dynamicRowsName];
                    this.sections().quantityPerSource.value(data);
                }
            },

            validateParent: function () {
                var formValid;
                _.each(this.sections(), function (section) {
                    switch (section.type()) {
                        case 'each':
                            if (!section.attribute()) {
                                throw new Error($.mage.__('Please, select attribute for the section ' + section.label));
                            }
                            break;
    
                        case 'single':
                            // console.log(section);
                            // let module = this.quantityResolver();
                            // if (!module.dynamicRowsCollection[module.dynamicRowsName]) {
                            //     throw new Error($.mage.__('Please fill in the values for the section ' + section.label));
                            // }
                            break;
                    }
                }, this);
                formValid = true;
                _.each($('[data-role=attributes-values-form]'), function (form) {
                    formValid = $(form).valid() && formValid;
                });
    
                if (!formValid) {
                    throw new Error($.mage.__('Please, fill correct values'));
                }
            },
    
            /** @inheritdoc */
            validate: function () {
                var valid = true,
                    quantityPerSource = this.quantityResolver();
    
                this.validateParent();
    
                if (this.type() && this.type() !== 'none') {
                    quantityPerSource.valid = true;
    
                    quantityPerSource.elems().forEach(function (item) {
                        quantityPerSource.validate.call(quantityPerSource, item);
                        // console.log(item);
                        //valid = valid && item.elems()[1].elems().length;
                    });
    
                    if (!quantityPerSource.valid || !valid) {
                        // console.log(quantityPerSource.valid);
                        throw new Error($.mage.__('Please fill-in correct values.'));
                    }
                }
            }

    });
});
