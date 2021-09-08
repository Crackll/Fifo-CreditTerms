<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Model\Source;

class DurationType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $data = [
                    [
                        'value' => 'd',
                        'label' => __('Per Day'),
                    ],
                    [
                        'value' => 'w',
                        'label' => __('Per Week'),
                    ],
                    [
                        'value' => 'm',
                        'label' => __('Per Month'),
                    ],
                    [
                        'value' => 'y',
                        'label' => __('Per Year'),
                    ],
            ];

        return $data;
    }
}
