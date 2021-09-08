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
namespace Webkul\MpPriceList\Model\Config;

class CalculationOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
                    ['value' => '1', 'label' => __('Increase Price')],
                    ['value' => '2', 'label' => __('Decrease Price')],
                ];

        return $data;
    }
}
