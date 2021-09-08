<?php

namespace Webkul\AccordionFaq\Block\Adminhtml\Addfaq\Edit;

use Magento\Backend\Block\Widget\Context;


use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class FAQ ResetButton
 */
class ResetButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Reset'),
            'class' => 'reset',
            'on_click' => 'location.reload();',
            'sort_order' => 30
        ];
    }
}
