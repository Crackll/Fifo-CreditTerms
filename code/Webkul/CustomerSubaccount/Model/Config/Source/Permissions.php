<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Permissions
 */
class Permissions implements OptionSourceInterface
{
    public function __construct(
        \Webkul\CustomerSubaccount\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->helper->getAllPermissions();
        $result = [];
        foreach ($options as $key => $value) {
            $result[] = ['value'=>$key, 'label'=>$value];
        }
        return $result;
    }
}
