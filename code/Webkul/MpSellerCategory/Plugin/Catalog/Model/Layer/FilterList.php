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
namespace Webkul\MpSellerCategory\Plugin\Catalog\Model\Layer;

class FilterList
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Webkul\MpSellerCategory\Helper\Data
     */
    protected $_helper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Webkul\MpSellerCategory\Helper\Data $_helper
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\MpSellerCategory\Helper\Data $helper
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
    }

    /**
     * aroundGetFilters Plugin
     *
     * @param \Magento\Catalog\Model\Layer\FilterList $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Layer $layer
     * @return array
     */
    public function aroundGetFilters(
        \Magento\Catalog\Model\Layer\FilterList $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Layer $layer
    ) {
        $result = $proceed($layer);
        if ($this->_helper->isAllowedSellerCategoryFilter()) {
            $result[] = $this->_objectManager->
            create(\Webkul\MpSellerCategory\Model\Layer\Filter\Category::class, ['layer' => $layer]);
        }

        return $result;
    }
}
