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
namespace Webkul\CustomerSubaccount\Block\Wishlist;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Block\Product\View;

class Item extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
{
    /**
     * Context
     *
     * @var \Magento\Catalog\Block\Product\Context
     */
    public $context;

    /**
     * Http Context
     *
     * @var \Magento\Framework\App\Http\Context
     */
    public $httpContext;

    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        array $data = [],
        ?ConfigInterface $config = null,
        ?UrlBuilder $urlBuilder = null
    ) {
        parent::__construct($context, $httpContext, $data, $config, $urlBuilder);
        $this->helper = $helper;
    }
    
    /**
     * Constructor
     *
     * @return \Webkul\CustomerSubaccount\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
