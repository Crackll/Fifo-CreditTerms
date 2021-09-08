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
namespace Webkul\MpPriceList\Ui\Component\Listing\Columns\Options\AdminGrid;

use Magento\Framework\Data\OptionSourceInterface;

class StatusType implements OptionSourceInterface
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
                            'label' =>  __('Active'),
                            'value' => 1
                        ],
                        [
                            'label' =>  __('Deactive'),
                            'value' => 2
                        ]
                    ];
        return $options;
    }
}
