/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Webkul_Marketplace/js/variations/steps/summary',
    'jquery',
    'mage/translate'
], function (Summary, $) {
    'use strict';

    return Summary.extend({
        defaults: {
            attributesName: [
                $.mage.__('Images'),
                $.mage.__('SKU'),
                $.mage.__('Quantity Per Source'),
                $.mage.__('Price')
            ],
            quantityFieldName: 'quantityPerSource'
        },

        generateGrid: function (variations, getSectionValue) {

            let self = this;
            var productSku = this.variationsComponent().getProductValue('name'),
                productPrice = this.variationsComponent().getProductValue('price'),
                productWeight = this.variationsComponent().getProductValue('weight'),
                variationsKeys = [],
                gridExisting = [],
                gridNew = [],
                gridDeleted = [];
            this.variations = [];

            _.each(variations, function (options) {
                var product, images, sku, quantity, price, variation,
                    productId = this.variationsComponent().getProductIdByOptions(options);

                if (productId) {
                    product = _.findWhere(this.variationsComponent().variations, {
                        productId: productId
                    });
                }
                images = getSectionValue('images', options);
                sku = productSku + _.reduce(options, function (memo, option) {
                    return memo + '-' + option.label;
                }, '');
                quantity = getSectionValue(self.quantityFieldName, options);

                if (!quantity && productId) {
                    quantity = product.quantity;
                }
                price = getSectionValue('price', options);

                if (!price) {
                    price = productId ? product.price : productPrice;
                }

                if (productId && !images.file) {
                    images = product.images;
                }
               
                variation = {
                    options: options,
                    images: images,
                    sku: sku,
                    quantity_per_source: quantity,
                    price: price,
                    productId: productId,
                    weight: productWeight,
                    editable: true
                };

                if (productId) {
                    variation.sku = product.name;
                    variation.weight = product.weight;
                    gridExisting.push(this.prepareRowForGrid(variation));
                } else {
                    gridNew.push(this.prepareRowForGrid(variation));
                }
                this.variations.push(variation);
                variationsKeys.push(this.variationsComponent().getVariationKey(options));
            }, this);

            this.gridExisting(gridExisting);
            this.gridExisting.columns(this.getColumnsName(this.wizard.data.attributes));

            if (gridNew.length > 0) {
                this.gridNew(gridNew);
                this.gridNew.columns(this.getColumnsName(this.wizard.data.attributes));
            }

            _.each(_.omit(this.variationsComponent().productAttributesMap, variationsKeys), function (productId) {
                gridDeleted.push(this.prepareRowForGrid(
                    _.findWhere(this.variationsComponent().variations, {
                        productId: productId
                    })
                ));
            }.bind(this));

            if (gridDeleted.length > 0) {
                this.gridDeleted(gridDeleted);
                this.gridDeleted.columns(this.getColumnsName(this.variationsComponent().productAttributes));
            }

        },

        prepareRowForGrid: function (variation) {
            var row = [];
            row.push(_.extend({
                images: []
            }, variation.images));
            row.push(variation.sku);
            var source = [];
            if ($.isArray(variation.quantity_per_source)) {
                variation.options.each(function(ind){
                    variation.quantity_per_source.each(function(index){
                        let code = index.source_code;
                        let name = `quantity_resolver[${ind.attribute_code}][${ind.label}][dynamicRows][${code}][quantity_per_source]`;
                        var values = $('input[name="'+name+'"]').map(function () {
                            return this.value;
                        }).get();
                        if (values.length == 0) {
                            values[1] = index.quantity();
                        }
                        source.push({
                            source_code:index.source_code,
                            name:index.name,
                            enabled:index.enabled,
                            country_id:index.country_id,
                            region_id:index.region_id,
                            region:index.region,
                            city:index.city,
                            street:index.street,
                            postcode:index.postcode,
                            use_default_carrier_config:index.use_default_carrier_config,
                            carrier_links:index.carrier_links,
                            extension_attributes:index.extension_attributes,
                            key:index.source_code,
                            quantity:values[1]                        
                        })
                    })
                })
                variation.quantity_per_source = source;
            }
            row.push(variation.quantity_per_source);
            _.each(variation.options, function (option) {
                row.push(option.label);
            });
            row.push(this.variationsComponent().getCurrencySymbol() +  ' ' + variation.price);

            return row;
        },
    });
});
