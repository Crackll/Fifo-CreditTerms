<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Model\Details\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class WholeSale ApprovalType
 */
class ApprovalType implements OptionSourceInterface
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

    protected function getOptionArray()
    {
        return [1 => __('Approved'), 0 => __('Pending')];
    }
}
