<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMSI
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/**
 * Marketplace Product variations matrix block.
 */
namespace Webkul\MpMSI\Block\Product\Edit\Variations\Config;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\View\Element\Template\Context;

class Matrix extends \Webkul\Marketplace\Block\Product\Edit\Variations\Config\Matrix
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_helperImage;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var Configurable
     */
    protected $_configurableProductType;

    /**
     * @var VariationMatrix
     */
    protected $_configurableProductVariationMatrix;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepositoryInterface;

    /**
     * @var StockRegistryInterface
     */
    protected $_stockRegistryInterface;

    private $_configurableProductMatrix;

    private $_configurableProductAttributes;

    /**
     * construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Helper\Image $helperImage
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType
     * @param \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $configurableProductVariationMatrix
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistryInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Helper\Image $helperImage,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        Configurable $configurableProductType,
        VariationMatrix $configurableProductVariationMatrix,
        \Magento\Framework\Registry $registry,
        ProductRepositoryInterface $productRepositoryInterface,
        StockRegistryInterface $stockRegistryInterface,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $helperImage,
            $configurableProductType,
            $configurableProductVariationMatrix,
            $registry,
            $productRepositoryInterface,
            $stockRegistryInterface,
            $data
        );
        $this->jsonHelper = $jsonHelper;
        $this->mpHelper = $mpHelper;
    }

    /**
     * Get Json Helper
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * Get Marketplace helper
     *
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }
}
