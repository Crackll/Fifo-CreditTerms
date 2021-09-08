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

namespace Webkul\MpWholesale\Model\Details\Source\QuotationStatus;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class WholeSale Types
 */
class Types implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    protected function getOptionArray()
    {
        return [1 => __('Pending'), 2 => __('Approved'), 3 => __('Declined')];
    }
}
