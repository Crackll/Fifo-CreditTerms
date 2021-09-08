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

class PriceType implements OptionSourceInterface
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
                            'label' =>  __('Fixed Price'),
                            'value' => 1
                        ],
                        [
                            'label' =>  __('Percent Price'),
                            'value' => 2
                        ]
                    ];
        return $options;
    }
}
