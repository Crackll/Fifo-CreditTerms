<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c)   Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Model\Placeholder\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Positions.
 */

/**
 *  Magento integration Magento test framework (MTF) bootstrap
 */
class Positions implements OptionSourceInterface
{

    /**
     * Get positions.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => "Please select a position",
                'value' => null
            ],
            [
                'label' => __("Home Seller Ads Page Top"),
                'value' => 1,
            ],
            [
                'label' => __("Home Seller Popup Ads"),
                'value' => 2,
            ],
            [
                'label' => __("Home Seller Ads Page Bottom Container"),
                'value' => 3,
            ],
            [
                'label' => __("Category Seller Ads Page Top"),
                'value' => 4,
            ],
            [
                'label' => __("Category Seller Ads Page Bottom Container"),
                'value' => 5,
            ],
            [
                'label' => __("Category Seller Ads Main"),
                'value' => 6,
            ],
            [
                'label' => __("Category Seller Ads Div Sidebar Main Before"),
                'value' => 7,
            ],
            [
                'label' => __("Category Seller Ads Div Sidebar Main After"),
                'value' => 8,
            ],
            [
                'label' => __("Catalog Product Seller Ads Page Top"),
                'value' => 9,
            ],
            [
                'label' => __("Catalog Product Seller Ads Page Bottom Container"),
                'value' => 10,
            ],
            [
                'label' => __("Home Seller Ads Product Main Info"),
                'value' => 11,
            ],
            [
                'label' => __("Catalog Search Seller Ads Page Top"),
                'value' => 12,
            ],
            [
                'label' => __("Catalog Search Seller Ads Page Bottom Container"),
                'value' => 13,
            ],
            [
                'label' => __("Catalog Search Seller Ads Main"),
                'value' => 14,
            ],
            [
                'label' => __("Catalog Search Seller Ads div Sidebar Main Before"),
                'value' => 15,
            ],
            [
                'label' => __("Catalog Search Seller Ads div Sidebar Main After"),
                'value' => 16,
            ],
            [
                'label' => __("Checkout cart Seller Ads Page Top"),
                'value' => 17,
            ],
            [
                'label' => __("Checkout cart Seller Ads Page bottom Container"),
                'value' => 18,
            ],
            [
                'label' => __("Checkout Seller Ads Page Top"),
                'value' => 19,
            ],
            [
                'label' => __("Checkout Seller Ads Page bottom Container"),
                'value' => 20,
            ]
        ];
        return $options;
    }
}
