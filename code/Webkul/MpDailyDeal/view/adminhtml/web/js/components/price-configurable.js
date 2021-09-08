define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    "jquery"
], function (_, registry, Abstract, $) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imports: {
                isConfigurable: '!ns = ${ $.ns }, index = configurable-matrix:isEmpty'
            },
            modules: {
                createConfigurableButton: '${$.createConfigurableButton}'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            // resolve initial disable state
            this.handlePriceValue(this.isConfigurable);
            // add listener to track "configurable" type
            this.setListeners({
                isConfigurable: 'handlePriceValue'
            });

            return this;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe(['content']);

            return this;
        },

        /**
         * Disable and clear price if product type changed to configurable
         *
         * @param {String} isConfigurable
         */
        handlePriceValue: function (isConfigurable) {
            this.disabled(!!this.isUseDefault() || isConfigurable);

            if (isConfigurable) {
                $('select[name="product[deal_status]"]').val(0);
                $('select[name="product[deal_status]"]').trigger('change');
                $('div[data-index="daily-deals"]').hide();
                this.clear();
            } else {
                $('div[data-index="daily-deals"]').show();
            }
        }
    });
});
