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
 * Marketplace block for fieldset of configurable product.
 */

namespace Webkul\MpMSI\Block\Product\Steps;

use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ProductFactory;

class Bulk extends \Webkul\Marketplace\Block\Product\Steps\Bulk
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes
     */
    protected $_mediaConfig;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_helperImage;

    /**
     * construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Image $helperImage
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        MediaConfig $mediaConfig,
        ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $helperImage,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context, $mediaConfig, $productFactory, $helperImage);
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Get json Helper
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
