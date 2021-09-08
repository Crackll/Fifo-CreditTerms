<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SampleProduct\Model\Product\Type;

class Sample extends \Magento\Catalog\Model\Product\Type\Simple
{
    /**
     * Return true if product has options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        return false;
    }

    /**
     * Check if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasRequiredOptions($product)
    {
        return false;
    }
}
