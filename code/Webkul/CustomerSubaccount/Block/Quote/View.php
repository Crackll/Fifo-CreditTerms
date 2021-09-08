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

namespace Webkul\CustomerSubaccount\Block\Quote;

use Magento\Downloadable\Model\Link;
use Magento\Store\Model\ScopeInterface;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * Context
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    public $context;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;
    
    /**
     * Quote
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    public $quoteFactory;

    /**
     * Pricing
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $pricingHelper;

    /**
     * Product Repo
     *
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;

    /**
     * Default Renderer
     *
     * @var \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
     */
    public $defaultRenderer;

    /**
     * Downloadable Product
     *
     * @var \Magento\Downloadable\Helper\Catalog\Product\Configuration
     */
    public $downloadableProductConfiguration;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $defaultRenderer
     * @param \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableProductConfiguration
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $defaultRenderer,
        \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableProductConfiguration,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->quoteFactory = $quoteFactory;
        $this->pricingHelper = $pricingHelper;
        $this->productRepository = $productRepository;
        $this->defaultRenderer = $defaultRenderer;
        $this->downloadableProductConfiguration = $downloadableProductConfiguration;
        parent::__construct($context, $data);
    }

    /**
     * Get Quote
     *
     * @return \Magento\Quote\Model\QuoteFactory
     */
    public function getQuote()
    {
        return $this->quoteFactory->create()->load($this->getRequest()->getParam('id'));
    }

    /**
     * Get Format Price
     *
     * @param int $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Get Product Url
     *
     * @param int $productId
     * @return string
     */
    public function getProductUrl($productId)
    {
        return $this->productRepository->getById($productId)->getProductUrl();
    }

    /**
     * Get Links Title
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getLinksTitle($product)
    {
        return $this->downloadableProductConfiguration->getLinksTitle($product) ?? $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Links
     *
     * @param Object $item
     * @return array
     */
    public function getLinks($item)
    {
        return $this->downloadableProductConfiguration->getLinks($item);
    }

    /**
     * Get Formated Option Value
     *
     * @param $optionValue
     * @return array
     */
    public function getFormatedOptionValue($optionValue)
    {
        return $this->defaultRenderer->getFormatedOptionValue($optionValue);
    }
}
