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
namespace Webkul\SampleProduct\ViewModel;

class FormViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Webkul\SampleProduct\Helper\Data
     */
    private $sampleProductHelper;

    public function __construct(
        \Magento\Catalog\Helper\Output $catalogHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\SampleProduct\Model\SampleProductFactory $sampleProductFactory,
        \Webkul\SampleProduct\Helper\Data $sampleProductHelper
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->priceHelper = $priceHelper;
        $this->productFactory = $productFactory;
        $this->sampleProductFactory = $sampleProductFactory;
        $this->sampleProductHelper = $sampleProductHelper;
    }

    public function getSampleProductHelper()
    {
        return $this->sampleProductHelper;
    }

    public function getCatalogHelper()
    {
        return $this->catalogHelper;
    }

    public function getSampleProduct($productId)
    {
        $collection = $this->sampleProductFactory->create()
            ->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 1);
        $sampleId = $collection->getSampleProductId($productId);
        return $this->productFactory->create()->load($sampleId);
    }

    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}
