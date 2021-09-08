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
namespace Webkul\MpPriceList\Block\Adminhtml\PriceList;

use Webkul\MpPriceList\Block\Adminhtml\Button;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CreateButton extends Button implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($this->getPriceList()->getId()) {
            return [
                'label' => __('Create New Price List'),
                'on_click' => sprintf("location.href = '%s';", $this->getCreateUrl()),
                'class' => 'primary'
            ];
        }
        return [];
    }

    /**
     * Get URL for create new Rule
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }
}
