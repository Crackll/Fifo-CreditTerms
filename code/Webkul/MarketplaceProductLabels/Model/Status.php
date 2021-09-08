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
 
class Status implements OptionSourceInterface
{
    /**
     * Get Grid row status type labels array.
     * @return array
     */
    public function getOptionArray()
    {
        $options = [
            \Webkul\MarketplaceProductLabels\Model\Label::STATUS_DISABLE => __('Disapproved'),
            \Webkul\MarketplaceProductLabels\Model\Label::STATUS_ENABLE => __('Approved'),''=>__('Select')
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
        $options = [
            \Webkul\MarketplaceProductLabels\Model\Label::STATUS_PENDING => __('Pending'),
            \Webkul\MarketplaceProductLabels\Model\Label::STATUS_DISABLE => __('Disapproved'),
            \Webkul\MarketplaceProductLabels\Model\Label::STATUS_ENABLE => __('Approved'),
            \Webkul\MarketplaceProductLabels\Model\Label::STATUS_DISAPPROVE => __('Disabled'),
            '' => __('Select')
        ];
        foreach ($options as $index => $value) {
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
