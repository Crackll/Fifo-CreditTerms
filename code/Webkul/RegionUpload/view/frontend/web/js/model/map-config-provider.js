/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    ['ko'],
    function (ko) {
        'use strict';
        var mapData = window.checkoutConfig.map;
        return {
            mapData: mapData,
            getMapData: function () {
                return mapData;
            }
        };
    }
);
