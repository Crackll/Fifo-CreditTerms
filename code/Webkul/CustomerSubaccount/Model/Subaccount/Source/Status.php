<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_CustomerSubaccount
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Model\Subaccount\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => __('Inactive'), 'value' => 0];
        $options[] = ['label' => __('Active'), 'value' => 1];
        return $options;
    }
}
