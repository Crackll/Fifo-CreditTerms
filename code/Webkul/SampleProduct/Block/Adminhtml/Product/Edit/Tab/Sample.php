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
namespace Webkul\SampleProduct\Block\Adminhtml\Product\Edit\Tab;

class Sample extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Webkul\SampleProduct\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\SampleProduct\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * isSampleBlockDisable
     */
    public function isSampleBlockAllowed()
    {
        $allowed = ['simple', 'configurable'];
        $productType = $this->coreRegistry->registry('product')->getTypeId();
        if (in_array($productType, $allowed) != false) {
            return true;
        }
    }

    /**
     * isSampleBlockDisable
     */
    public function isSampleBlockDisable()
    {
        $unAllowed = ['downloadable', 'sample'];
        $productType = $this->coreRegistry->registry('product')->getTypeId();
        if (in_array($productType, $unAllowed) != false) {
            return true;
        }
    }

    /**
     * Retrieve currency Symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency(
            $this->getBaseCurrencyCode()
        )->getSymbol();
    }

    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * getSampleData
     *
     * @return /Webkul/SampleProduct/Model/SampleProduct
     */
    public function getSampleData()
    {
        $productId = $this->coreRegistry->registry('product')->getId();
        return $this->helper->getSampleProductByProductId($productId);
    }

    public function getProductId()
    {
        return $productId = $this->coreRegistry->registry('product')->getId();
    }
}
