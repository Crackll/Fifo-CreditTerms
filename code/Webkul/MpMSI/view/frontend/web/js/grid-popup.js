define([
    'uiComponent',
    'uiLayout',
    'uiRegistry',
    'mage/translate',
    'mage/template',
    'jquery',
    'ko'
], function(
    Component,
    layout,
    registry,
    $t,
    mageTemplate,
    $,
    ko
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: "Webkul_MpMSI/grid-popup.html",
            productData:{},
            products: [],
            totalCount:0,
            productSelector: false,
            parentComponent: {},
            productSelectedCheckbox:[],
            allSelectedCheckbox:[],
            productSelectedRadio:'',
            pageNumber: 1,
            searchString: '',
            pages:0,
            stores:window.stores,
            currencies:window.currencies
        },

        /**
         * @extends
         */
        initialize: function (config) {
            var self = this;
            this._super();
            this.parentComponent = config.provider;
           
            self.productSelector = config.provider.inputType;
            self.totalCount(self.productData.total_count);
           
            self.products(self.productData.items);
            if (self.products().length == 0) {
                self.products().length = null;
            }
            self.pages(Math.ceil(self.totalCount()/20));
            window.gridPopup = this;


            self.allSelectedCheckbox.subscribe(function (ids) {
                self.ids = [];
                if(ids.length >= 1) {
                    var prodArray = self.products();
                    prodArray.forEach(function(prod){
                        if(prod) {
                            self.ids.push(prod.source_code)
                        }
                    })

                } else {
                    self.ids = [];
                }
                self.productSelectedCheckbox(self.ids);
            });
            self.productSelectedCheckbox.subscribe(function (ids) {
                self.parentComponent.sourcesIds(ids.join(","));
            });
            self.productSelectedRadio.subscribe(function (id) {
                self.parentComponent.sourcesIds(id);
            });
            self.searchString.subscribe(function(q) {
                if (q.length > 2 || q.length == 0) {
                    self.searchProduct(q);
                }
            });

            self.pageNumber.subscribe(function(n) {
                //console.log(n);
                if (n < 1) {
                    self.pageNumber(1);
                    return;
                } else if (n > self.pages()) {
                    self.pageNumber(self.pages());
                    return;
                }
                
                let promise = self.getProductCollection('');
                promise.then(function(response) {
                    self.products(response.items);
                    self.totalCount(response.total_count);
                }, function(error) {
                    console.log(error);
                });
            });

            self.totalCount.subscribe(function (n) {
                self.pages(Math.ceil(n/20));
                self.pageNumber(1);
            });

            window.gridPopup = this;
        },

        /**
         * initialize observers
         */
        initObservable: function () {
            this._super().observe(
                'totalCount products productSelectedCheckbox allSelectedCheckbox productSelectedRadio pageNumber searchString pages stores currencies displayMessage'
            );
            return this;
        },

        prevPage: function() {
            let self = this;
            let pageNumber = parseInt(self.pageNumber());
            if (pageNumber <= 1) {
                self.pageNumber(1);
            } else {
                self.pageNumber(pageNumber-1);
            }
        },

        isFirst: function() {
            let self = this;
            if (self.pageNumber() == 1) {
                return true;
            }

            return false;
        },

        isLast: function() {
            let self = this;
            
            if (self.pageNumber() == self.pages()) {
                return true;
            }

            return false;
        },

        nextPage: function() {
            let self = this;
            let pageNumber = parseInt(self.pageNumber());
            
            self.pageNumber(pageNumber+1);
            
        },

        searchProduct: function(filter) {
            let self = this;
            if (!filter) {
                filter = $('.no-changes').val();
            }
            let promise = self.getProductCollection(filter);
            promise.then(function(response) {
                self.products(response.items);
                self.totalCount(response.total_count);
            }, function(error) {
                console.log(error);
            });
        },

        getProductCollection: function(filter) {
            let self = this;
            let promise = new Promise(function(resolve, reject) {
                $.ajax({
                    url: window.ajaxProductListUrl,
                    type: 'GET',
                    dataType: 'json',
                    showLoader: true,
                    headers: {
                        "Content-Type": "application/json"
                    },
                    data: {
                        pageSize: 20,
                        pageNumber: parseInt(self.pageNumber()),
                        filter: filter,
                        sort:'name',
                        type: $('.filter-product-type').val()
                    },
                    success: function(response) {
                        resolve(response);
                    },
                    error: function() {
                        reject("some error occured");
                    }
                });
            });

            return promise;

        }


    });
});