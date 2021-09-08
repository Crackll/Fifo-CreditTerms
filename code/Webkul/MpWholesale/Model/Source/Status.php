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

class Status implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $data = [
                    [
                        'value' => 1,
                        'label' => 'Enabled',
                    ],
                    [
                        'value' => 0,
                        'label' => 'Disabled',
                    ],
            ];

        return $data;
    }
}
