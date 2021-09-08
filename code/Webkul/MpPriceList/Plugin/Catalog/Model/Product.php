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
namespace Webkul\MpPriceList\Plugin\Catalog\Model;

use Magento\Catalog\Model\Product as CatalogProduct;

class Product
{
    /**
     * @var \Webkul\MpPriceList\Helper\Data
     */
    private $_priceListHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Webkul\MpPriceList\Helper\Data $priceListHelper
     */
    public function __construct(
        \Webkul\MpPriceList\Helper\Data $priceListHelper
    ) {
        $this->_priceListHelper = $priceListHelper;
    }

    /**
     * get price
     *
     * @param CatalogProduct $subject
     * @param array $result
     * @return void
     */
    public function afterGetPrice(CatalogProduct $subject, $result)
    {
        if ($this->_priceListHelper->isModuleEnabled() && $subject->getId()) {
            return $this->_priceListHelper->getPrice($subject, $result);
        }
        return $result;
    }
}
