<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model;

class Dateformat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'd/m/y', 'label' => __('D/M/Y')], ['value' => 'y/m/d', 'label' =>
        __('Y/M/D')],['value' => 'm/d/y', 'label' => __('M/D/Y')]];
    }
}
