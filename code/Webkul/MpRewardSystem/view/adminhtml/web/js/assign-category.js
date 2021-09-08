/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

define([
    'mage/adminhtml/grid'
], function () {
    'use strict';

    return function (config) {
        var rewardCategories = $H({}),
            gridJsObject = window[config.gridJsObjectName],
            length = rewardCategories.keys().length;

        $('in_reward_category').value = Object.toJSON(rewardCategories);

        /**
         * Register Category Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function rewardCategoryCheckBoxClick(grid, element, checked)
        {
            if (element.className != "admin__control-checkbox") {
                if (checked) {
                    rewardCategories.set(element.value, length+1);
                } else {
                    rewardCategories.unset(element.value);
                }
                length = rewardCategories.keys().length;
                $('in_reward_category').value = Object.toJSON(rewardCategories);
                grid.reloadParams = {
                    'selected_products[]': rewardCategories.keys()
                };
            }
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function rewardCategoryRowClick(grid, event)
        {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;
            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }
        gridJsObject.rowClickCallback = rewardCategoryRowClick;
        gridJsObject.checkboxCheckCallback = rewardCategoryCheckBoxClick;
    };
});
