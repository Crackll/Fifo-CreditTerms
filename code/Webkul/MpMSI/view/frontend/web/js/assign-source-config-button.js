define([
    'Webkul_MpMSI/js/assign-source',
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

        default: {
            gridDisplayArea:"component-grid-configurable"
        },

        /**
         * @extends
         */
        initialize: function () {
            var self = this;
            this._super();
        },

        initProductButton: function() {
            var self = this;
            self.inputType(arguments[0].productInputType?arguments[0].productInputType:false);
            let childComponent = registry.get(self.childComponent);
            var options = {
                autoOpen: true,
                modalClass: this.modalClass,
                buttons: [{
                    text: $t("Done"),
                    class: 'button primary',
                    click: function() {
                        childComponent.addRow();
                        this.closeModal();
                    } 
                }],
                closed: function() {
                    $("body").find(".wk-modal-item-simple").remove();
                },
                clickableOverlay: false,
                title: $t(self.gridPopupTitle),
                type: "slide"
            };

            if ($("body").find("."+this.modalClass).length == 0) {
            
                this.gridDisplayArea = "component-grid-configurable";
                let sourceItems = window.sources.items.filter(item => childComponent.sourcesIds().indexOf(item.source_code) === -1 );
                let sourcesUpdated = jQuery.extend(true, {}, window.sources);
                sourcesUpdated.items = sourceItems;
                self.popupGridName = this.name + '-'+this.gridDisplayArea+Math.floor((Math.random() * 100) + 1);
                var gridComponentParams = {
                    parent: this.name,
                    name: self.popupGridName,
                    displayArea: this.gridDisplayArea,
                    component: this.gridPopupComponent,
                    provider:this,
                    config: {
                        productData: sourcesUpdated
                    }
                };
                layout([gridComponentParams]);
            }
            
            let gridContainerClass = (self.name+self.popupGridName).replace(/\./g, '').replace(/ /g, '');
    
            this._isElementLoaded('.'+gridContainerClass, function() {

                let modalHtml = $('.'+gridContainerClass);
                if (modalHtml[1]) {
                    modalHtml[1].remove();
                }
                //$('.grid-popup-container').remove();
                var popup = modal(options, modalHtml);
                popup.openModal();
            });
        },

        addEvents: function () {
            //do nothing
        }
    });
});
