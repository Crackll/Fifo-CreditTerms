<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceProductLabels\Model;

use Magento\Framework\Data\OptionSourceInterface;
 
class Position implements OptionSourceInterface
{
    /**
     * Get Grid row status type labels array.
     * @return array
     */
    public function getOptionArray()
    {
        $options = [
            \Webkul\MarketplaceProductLabels\Model\Label::POSITION_TOP_LEFT => __('Top-Left'),
            \Webkul\MarketplaceProductLabels\Model\Label::POSITION_TOP_RIGHT => __('Top-Right'),
            \Webkul\MarketplaceProductLabels\Model\Label::POSITION_BOTTOM_LEFT => __('Bottom-Left'),
            \Webkul\MarketplaceProductLabels\Model\Label::POSITION_BOTTOM_RIGHT => __('Bottom-Right')
        ];
        return $options;
    }
 
    /**
     * Get Grid row status labels array with empty value for option element.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }
 
    /**
     * Get Grid row type array for option element.
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
 
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
