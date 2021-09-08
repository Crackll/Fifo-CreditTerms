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

namespace Webkul\MpMSI\Block\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Webkul\Marketplace\Model\SaleslistFactory as MpSalesList;

class Productlist extends \Webkul\Marketplace\Block\Product\Productlist
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /** @var \Magento\Catalog\Model\Product */
    protected $_productlists;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var MpProductCollection
     */
    protected $mpProductCollection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var MpSalesList
     */
    protected $mpSalesList;

    /**
     * construct
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Webkul\MpMSI\Helper\Data $msiHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollection
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\Marketplace\Model\SaleslistFactory $mpSalesList
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpMSI\Helper\Data $msiHelper,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $productCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        MpHelper $mpHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        MpProductCollection $mpProductCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        MpSalesList $mpSalesList,
        array $data = []
    ) {
        $this->msiHelper = $msiHelper;
        parent::__construct(
            $context,
            $customerSession,
            $productCollectionFactory,
            $priceCurrency,
            $mpHelper,
            $eavAttribute,
            $mpProductCollection,
            $productFactory,
            $mpSalesList,
            $data
        );
    }

    /**
     * Get Marketplace helper
     */
    public function getMsiHelper()
    {
        return $this->msiHelper;
    }

    /**
     * Get MSI helper
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }
}
