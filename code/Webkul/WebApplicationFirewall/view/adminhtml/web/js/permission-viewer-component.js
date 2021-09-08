/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Ui/js/modal/alert'
], function ($, Component, ko, alert) {
    'use strict';

    var _config = {};
    var _contents = [];
    var _path = "./";
    var _pathFormVisibility = false;

    return Component.extend({
        defaults: {
            template: 'Webkul_WebApplicationFirewall/permission-viewer'
        },

        initialize: function (config) {
            _config = config;
            _contents = ko.observableArray([]);
            _pathFormVisibility = ko.observable(false);

            $(document).ready(function () {
                $("#hosting-selector").change(function () {
                    var type = $(this).val();
                    if (type == 'shared' || type == 'private') {
                        $("#serverType").val(type);
                        _pathFormVisibility(1);
                    } else {
                        $("#serverType").val('');
                        _contents.removeAll();
                        _pathFormVisibility(0);
                    }

                    if ($("#serverType").val()) {
                        $("#directoryPath").val('.');
                        $("#pathForm").submit();
                    }
                });
            });

            this._super();
            return this;
        },

        pathFormVisibility: function () {
            return _pathFormVisibility;
        },

        getHeading: function () {
            return $.mage.__('Directories/Files Path');
        },

        submitPath: function () {
            var path = $("#directoryPath").val();
            var dirInfoAjaxUrl = _config.dirInfoAjaxUrl;
            $('body').trigger('processStart');
            new Ajax.Request(dirInfoAjaxUrl, {
                method: 'POST',
                parameters: {
                    path: path,
                    serverType: $("#serverType").val()
                },
                onSuccess: function (response) {

                    var response = response.responseJSON;
                    if (response.status == 'success') {
                        var dirInfo = JSON.parse(response.data);
                        _contents.removeAll();
                        dirInfo.forEach(function (info) {
                            _contents.push(info);
                        });

                        $("#directoryPath").val(path);
                    } else {
                        alert({
                            title: $.mage.__('Complete Permissions Viewer'),
                            content: $.mage.__('Path Not Found')
                        });
                    }
                    $('body').trigger('processStop');
                },
                onFailure: function (response) {
                    alert({
                        title: $.mage.__('Complete Permissions Viewer'),
                        content: $.mage.__('Path Not Found')
                    });

                    $('body').trigger('processStop');
                }
            });
        },

        contents: function () {
            return _contents;
        },

        contentsLength: function () {
            return _contents().length;
        },

        initObservable: function () {
            return this;
        },

        getContents: function (data) {
            var dirInfoAjaxUrl = _config.dirInfoAjaxUrl;
            $('body').trigger('processStart');
            new Ajax.Request(dirInfoAjaxUrl, {
                method: 'POST',
                parameters: {
                    path: data.path,
                    serverType: $("#serverType").val()
                },
                onSuccess: function (response) {
                    var response = response.responseJSON;
                    if (response.status == 'success') {
                        var dirInfo = JSON.parse(response.data);
                        _contents.removeAll();
                        dirInfo.forEach(function (info) {
                            _contents.push(info);
                        });

                        $("#directoryPath").val(data.path);
                    } else {
                        alert({
                            title: $.mage.__('Complete Permissions Viewer'),
                            content: $.mage.__('Path Not Found')
                        });
                    }
                    $('body').trigger('processStop');
                },
                onFailure: function (response) {
                    alert({
                        title: $.mage.__('Complete Permissions Viewer'),
                        content: $.mage.__('Path Not Found')
                    });

                    $('body').trigger('processStop');
                }
            });
        }


    });
});
