<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Ui\Component\Listing\Columns\Options;

use Magento\Framework\Data\OptionSourceInterface;

class ApplicableType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
                        [
                            'label' =>  __('Product'),
                            'value' => 1
                        ],
                        [
                            'label' =>  __('Category'),
                            'value' => 2
                        ],
                        [
                            'label' =>  __('Product Quantity'),
                            'value' => 3
                        ],
                        [
                            'label' =>  __('Total Product Price'),
                            'value' => 4
                        ]
                        
                    ];
        return $options;
    }
}
