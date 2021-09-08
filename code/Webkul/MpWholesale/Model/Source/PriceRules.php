<?php

namespace Webkul\MpWholesale\Model\Source;

class PriceRules implements \Magento\Framework\Option\ArrayInterface
{
    /**
     */
    protected $helper;

    /**
     * @param Webkul\MpWholesale\Data\Helper $helper
     */
    public function __construct(
        \Webkul\MpWholesale\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function toOptionArray()
    {
        $rulesList = $this->helper->getRulesList();
        return $rulesList;
    }
}
