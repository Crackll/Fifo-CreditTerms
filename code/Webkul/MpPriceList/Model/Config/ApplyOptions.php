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

class ApplyOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
                    ['value' => '1', 'label' => __('Product')],
                    ['value' => '2', 'label' => __('Category')],
                    ['value' => '3', 'label' => __('Product Quantity')],
                    ['value' => '4', 'label' => __('Total Product Price')],
                ];

        return $data;
    }
}
