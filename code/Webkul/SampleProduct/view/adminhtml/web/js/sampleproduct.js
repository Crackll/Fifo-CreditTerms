/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    'jquery',
    'mage/template'
], function($, mageTemplate) {
    'use strict';
    $.widget('mage.sampleProduct', {
        _create: function () {
            if (parseInt($('#wk-sample-status').val())) {
                var progressTmpl = mageTemplate('#sampleproduct-template'),
                            tmpl;
                tmpl = progressTmpl({
                    data: {}
                });

                $(tmpl).insertAfter('[data-index="wk-sample-status"]');
            } else {
                $('[data-index="wk-sample-title"]').remove();
                $('[data-index="wk-sample-price"]').remove();
                $('[data-index="wk-sample-qty"]').remove();
            }

            $('body').on('change', '#wk-sample-status', function () {
                if (parseInt($(this).val())) {
                    var progressTmpl = mageTemplate('#sampleproduct-template'),
                                tmpl;
                    tmpl = progressTmpl({
                        data: {}
                    });

                    $(tmpl).insertAfter('[data-index="wk-sample-status"]');
                } else {
                    $('[data-index="wk-sample-title"]').remove();
                    $('[data-index="wk-sample-price"]').remove();
                    $('[data-index="wk-sample-qty"]').remove();
                }
            });
        }
    });
    return $.mage.sampleProduct;
});
