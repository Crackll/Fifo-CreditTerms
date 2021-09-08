<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Priority extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $retrunArray = [
            0 => __('Product Based'),
            1 => __('Cart Based'),
            2 => __('Category Based')
        ];
        return $retrunArray;
    }
}
