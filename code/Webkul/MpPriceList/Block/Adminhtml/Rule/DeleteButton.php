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
namespace Webkul\MpPriceList\Block\Adminhtml\Rule;

use Webkul\MpPriceList\Block\Adminhtml\Button;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends Button implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($this->getRule()->getId()) {
            return [
                'label' => __('Delete Rule'),
                'on_click' => sprintf("location.href = '%s';", $this->getDeleteRuleUrl()),
            ];
        }
        return [];
    }

    /**
     * Get URL for delete Rule
     *
     * @return string
     */
    public function getDeleteRuleUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getRule()->getId()]);
    }
}
