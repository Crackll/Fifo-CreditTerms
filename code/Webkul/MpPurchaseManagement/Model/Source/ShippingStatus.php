<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OrderStatus
 */
class ShippingStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [0 => __('Cancelled'), 1 => __('Processing'), 2 => __('Shipped'), 3 => __('Received')];
    }
}
