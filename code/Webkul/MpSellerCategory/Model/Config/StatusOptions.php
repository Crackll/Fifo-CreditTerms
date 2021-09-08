<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Model\Config;

class StatusOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\MpSellerCategory\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Webkul\MpSellerCategory\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpSellerCategory\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $allStatus = $this->_helper->getAllStatus();
        $options = [];
        foreach ($allStatus as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }
}
