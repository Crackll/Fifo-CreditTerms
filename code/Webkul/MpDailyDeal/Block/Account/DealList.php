<?php
 /**
  * Webkul_MpDailyDeal add Deal layout page.
  * @category  Webkul
  * @package   Webkul_MpDailyDeal
  * @author    Webkul
  * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\MpDailyDeal\Block\Account;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Webkul\Marketplace\Model\SaleslistFactory as MpSalesList;

class DealList extends \Webkul\Marketplace\Block\Product\Productlist
{
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
    /**
     * @var MpHelper
     */
    protected $mpHelper;
    /**
     * @var helper
     */
    protected $helper;
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
    protected $_productList;
    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $productCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        MpHelper $mpHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        MpProductCollection $mpProductCollection,
        \Webkul\MpDailyDeal\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        MpSalesList $mpSalesList,
        \Webkul\Marketplace\Helper\Data $mphelper,
        array $data = []
    ) {
        $this->imageHelper = $context->getImageHelper();
        $this->_customerSession = $customerSession;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_imageHelper = $context->getImageHelper();
        $this->_priceCurrency = $priceCurrency;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->eavAttribute = $eavAttribute;
        $this->mpProductCollection = $mpProductCollection;
        $this->productFactory = $productFactory;
        $this->mpSalesList = $mpSalesList;
        $this->_objectManager = $objectManager;
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
    public function getAllProducts()
    {
        $localeDate = $this->_objectManager->get(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        );
        $today = $localeDate->date(strtotime('-2 day'))->format('Y-m-d H:i:s');
        $collection = parent::getAllProducts();
        if ($collection && count($collection)>0) {
            $proIds = $collection->getAllIds();

            if (!$this->_productList && !empty($proIds)) {
                $this->_productList = $this->_productCollectionFactory->create()
                                    ->addAttributeToSelect('*')
                                    ->addFieldToFilter('entity_id', ['in' => $proIds])
                                    ->addAttributeToFilter('deal_to_date', ['gteq' => $today])
                                    ->addAttributeToFilter('deal_status', ['neq' => 0])
                                    ->addFieldToFilter('type_id', ['nin'=> ['grouped','configurable']])
                                    ->setOrder('entity_id', 'desc');
            }
            return $this->_productList;
        } else {
            return false;
        }
    }

    /**
     * @param int $productId
     * @return url string add deal on product
     */
    public function getAddDealUrl($productId)
    {
        return $this->getUrl(
            'mpdailydeal/account/adddeal',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id'=>$productId
            ]
        );
    }

    /**
     * getDateTimeAsLocale
     * @param string $data in base Time zone
     * @return string date in current Time zone
     */
    public function getDateTimeAsLocale($data)
    {
        if ($data) {
            return $this->_localeDate->date(new \DateTime($data))->format('m/d/Y H:i:s');
        }
        return $data;
    }
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'deal.product.list.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * @return integer
     */
    public function getUtcOffset($date)
    {
        return timezone_offset_get(new \DateTimeZone($this->_localeDate->getConfigTimezone()), new \DateTime($date));
    }

    public function getDealHelper()
    {
        return $this->helper;
    }

    public function geMpHelper()
    {
        return $this->mpHelper;
    }
}
