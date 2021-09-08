<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomerSubaccount\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Common implements ArgumentInterface
{
    /**
     * @var helper
     */
    public $helper;

    /**
     * @param helper $helper
     */
    public function __construct(
        \Webkul\CustomerSubaccount\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function getHelper()
    {
        return $this->helper;
    }
}
