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
            template: "Webkul_MpMSI/assign-sources.html",
            inputType: false,
            sourcesIds: '',
            gridPopupComponent: 'Webkul_MpMSI/js/grid-popup',
            gridDisplayArea: "component-grid",
            gridPopupTitle: "Assign Sources",
            buttonTitle: $.mage.__('Assign Sources'),
            currentSourceStocks: {},
            savedSourceCodes: [],
            rowIndex: 0,
            modalClass: "wk-modal-item-simple",
            popupGridName: ''
        },

        /**
         * @extends
         */
        initialize: function() {
            var self = this;
            this._super();
            jQuery("[name='product[quantity_and_stock_status][qty]']").parent().parent().remove();
            //jQuery("[name='product[quantity_and_stock_status][is_in_stock]']").parent().parent().remove();
            if (this.productType == "configurable") {
                $("#msi-container").hide();
            }
            
            if (this.currentSourceStocks.length > 0) {
                this.savedSourceCodes = this.currentSourceStocks.map(item => item.source_code);
                let sourceItems = window.sources.items.map(function(item) {
                    if (this.savedSourceCodes.indexOf(item.source_code) !== -1) {
                        item.index = this.rowIndex++;
                        if (item.enabled) {
                            item.enabled = $.mage.__("Enable");
                        } else {
                            item.enabled = $.mage.__("Disable");
                        }
                        item.quantity = this.getQuantityBySourceCode(item.source_code);
                        item.status = this.getStatusBySourceCode(item.source_code);
                        return item;
                    }
                }.bind(this));
                var tmpl = mageTemplate('#assigned-sources-list-template', {
                    data: {
                        sources: sourceItems.filter(item => item !== undefined)
                    }
                });
                $(".assinged-sources-container").html(tmpl);
                self.addEvents();
            }
        },

        /**
         * initialize observers
         */
        initObservable: function() {
            this._super().observe('inputType sourcesIds buttonTitle');
            $('select[name="product[quantity_and_stock_status][is_in_stock]"]').closest('.field').css("display","none");
            return this;
        },

        getQuantityBySourceCode: function(sourceCode) {
            if (this.currentSourceStocks.length > 0) {

                for (let i = 0; i < this.currentSourceStocks.length; i++) {
                    if (this.currentSourceStocks[i].source_code == sourceCode) {
                        return this.currentSourceStocks[i].quantity;
                    }
                }
            }
            return false;
        },

        getStatusBySourceCode: function(sourceCode) {
            if (this.currentSourceStocks.length > 0) {

                for (let i = 0; i < this.currentSourceStocks.length; i++) {
                    if (this.currentSourceStocks[i].source_code == sourceCode) {
                        return this.currentSourceStocks[i].status;
                    }
                }
            }
            return 0;
        },

        initProductButton: function() {
            var self = this;
            self.inputType(arguments[0].productInputType ? arguments[0].productInputType : false);

            var options = {
                autoOpen: true,
                modalClass: this.modalClass,
                buttons: [{
                    text: $t("Done"),
                    class: 'button primary',
                    click: function() {
                        let sourcesIds = $("input[name=sourcesIds]").val().split(",");
                        let selectedSources = window.sources.items.map((item) => {
                            if (sourcesIds.indexOf(item.source_code) !== -1) {
                                item.index = self.rowIndex++;
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
                        for (let i = 0; i < selectedSources.length; i++) {
                            if(selectedSources[i] != '') {
                                selectedSources[i].quantity = null
                            }
                        }
                        selectedSources = selectedSources.filter((item) => {
                            if (item) {
                                self.savedSourceCodes.push(item.source_code);
                                return true;
                            } else {
                                return false;
                            }
                        });

                        var tmpl = mageTemplate('#assigned-sources-list-template', {
                            data: {
                                sources: selectedSources
                            }
                        });
                        $(".assinged-sources-container").append(tmpl);
                        $("input[name=sourcesIds]").val('');
                        self.addEvents.call(self);
                        this.closeModal();
                    }
                }],
                closed: function() {
                    $("body").find(".wk-modal-item-simple").remove();
                },
                clickableOverlay: false,
                title: $.mage.__(self.gridPopupTitle),
                type: "slide"
            };
            if ($("body").find("." + this.modalClass).length == 0) {

                let sourceItems = window.sources.items.filter(item => this.savedSourceCodes.indexOf(item.source_code) === -1);
                let sourcesUpdated = jQuery.extend(true, {}, window.sources);
                sourcesUpdated.items = sourceItems;
                self.popupGridName = this.name + '-' + this.gridDisplayArea + Math.floor((Math.random() * 100) + 1);
                var gridComponentParams = {
                    parent: this.name,
                    name: self.popupGridName,
                    displayArea: this.gridDisplayArea,
                    component: this.gridPopupComponent,
                    provider: this,
                    config: {
                        productData: sourcesUpdated
                    }
                };
                layout([gridComponentParams]);
            }
            let gridContainerClass = (self.name + self.popupGridName).replace(/\./g, '').replace(/ /g, '');
            this._isElementLoaded('.' + gridContainerClass, function() {
                $('.' + gridContainerClass)
                let modalHtml = $('.' + gridContainerClass);
                if (modalHtml[1]) {
                    modalHtml[1].remove();
                }
                //$('.grid-popup-container').remove();
                var popup = modal(options, modalHtml);
                popup.openModal();
            });
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

        _isElementLoaded: function($element, callback) {
            var self = this;
            setTimeout(function() {

                if ($($element).is(':visible')) {
                    callback();
                } else {
                    self._isElementLoaded($element, callback);
                }
            }, 50);
        },

        initCollectionButton: function() {
            this.initProductButton({ productInputType: true });
        }

    });
});