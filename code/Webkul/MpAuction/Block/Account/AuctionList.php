<?php
 /**
  * Webkul_MpAuction Auction List Block.
  * @category  Webkul
  * @package   Webkul_MpAuction
  * @author    Webkul
  * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\MpAuction\Block\Account;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpAuction\Model\ProductFactory as AuctionProduct;
use Webkul\MpAuction\Model\ResourceModel\Product\Source\AuctionStatus;
use Webkul\MpAuction\Model\ResourceModel\Product\Source\Options as ProductOptions;
use Webkul\MpAuction\Helper\Data as AuctionHelperData;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\Marketplace\Helper\Data as MpHelperData;
use Magento\Framework\App\RequestInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EntityAttribute;
use Webkul\Marketplace\Model\ResourceModel\Product\Collection;
use Webkul\Marketplace\Model\Product as MarketplaceProduct;

class AuctionList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProduct;
    
    private $mpHelperData;
    /**
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Webkul\MpAuction\Model\ResourceModel\Product\Source\AuctionStatus
     */
    private $auctionStatus;

    /**
     * @var Webkul\MpAuction\Helper\Data
     */
    private $auctionHelperData;

    /**
     * @var Webkul\MpAuction\Model\ResourceModel\Product\Source\Options
     */
    private $productOptions;
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    protected $_productList;

     /**
      * @var \Magento\Catalog\Helper\Image
      */
    protected $imageHelper;
    /**
     * @var EntityAttribute
     */
    private $entityAttribute;
    /**
     * @var Collection
     */
    private $collection;
    /**
     * @var MarketplaceProduct
     */
    private $marketplaceProduct;

    /**
     * @param Context                                   $context
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param CollectionFactory                         $productCollectionFactory
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        CustomerSession $customerSession,
        CollectionFactory $productCollectionFactory,
        AuctionProduct $auctionProduct,
        ProductRepositoryInterface $productRepository,
        AuctionStatus $auctionStatus,
        AuctionHelperData $auctionHelperData,
        ProductOptions $productOptions,
        TimezoneInterface $localeDate,
        PriceCurrencyInterface $priceCurrency,
        MpHelperData $mpHelperData,
        RequestInterface $request,
        EntityAttribute $entityAttribute,
        Collection $collection,
        MarketplaceProduct $marketplaceProduct,
        array $data = []
    ) {
        $this->collection = $collection;
        $this->mpHelperData = $mpHelperData;
        $this->auctionProduct = $auctionProduct;
        $this->productRepository = $productRepository;
        $this->auctionStatus = $auctionStatus;
        $this->customerSession = $customerSession;
        $this->imageHelper = $context->getImageHelper();
        $this->auctionHelperData = $auctionHelperData;
        $this->productOptions = $productOptions;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->localeDate = $localeDate;
        $this->_priceCurrency = $priceCurrency;
        $this->request= $request;
        $this->entityAttribute = $entityAttribute;
        $this->marketplaceProduct = $marketplaceProduct;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        if (!$this->_productList) {
            $collPro = $this->getAllMpProducts();
            $mpProArray = [0];
            foreach ($collPro as $mpProduct) {
                array_push($mpProArray, $mpProduct->getMageproductId());
            }
            $search = $this->getRequest()->getParam('s');
            $search = filter_var($search, FILTER_SANITIZE_STRING);
            $search = trim($search);
            $collection = $this->productCollectionFactory->create()
                                ->addFieldToFilter('name', ['like' => '%'.$search.'%'])
                                ->addFieldToFilter('type_id', ['nin'=> ['grouped', 'configurable','rental']])
                                ->addFieldToFilter('visibility', ['neq'=>1]);
                                
            $aucProArray = [0];
            foreach ($collection as $aucPro) {
                array_push($aucProArray, $aucPro->getEntityId());
            }
            $this->_productList = $this->auctionProduct->create()->getCollection()
                ->addFieldToFilter('customer_id', $this->auctionHelperData->getCurrentCustomerId())
                ->addFieldToFilter('product_id', ['in' => $aucProArray])
                ->setOrder('entity_id', 'AESC');
        }
        return $this->_productList;
    }

    public function imageHelperObj()
    {
        return $this->imageHelper;
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
                'auction.pro.list.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
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
     * @param int $productId
     * @return url string add deal on product
     */
    public function getAddAuctionUrl($productId)
    {
        return $this->getUrl(
            'mpauction/account/addauction',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'aid'=>$productId
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
            return date_format(date_create($data), "m/d/Y H:i:s");
        }
        return $data;
    }

    /**
     * getProductDetail
     * @param int $productId which detail want
     * @return false| Magento\Catalog\Model\Product
     */
    public function getProductDetail($productId)
    {
        if ($productId) {
            return $this->productRepository->getById($productId);
        }
        return false;
    }

    /**
     * getAuctionStatusLabel
     * @param int $statusVal of auction
     * @return false| string
     */
    public function getAuctionStatusLabel($statusVal)
    {
        if ($statusVal != '') {
            $options = $this->auctionStatus->getOptionArray();
            return $options[$statusVal];
        }
        return false;
    }

    /**
     * getAucProSoldStatus
     * @param int $auctionId
     * @return false| string
     */
    public function getAucProSoldStatus($auctionId)
    {
        $winBidData = $this->auctionHelperData->getWinnerBidDetail($auctionId);
        $options = $options = ['0'=>__('No'),'1'=>__('Yes')];

        if ($winBidData && $winBidData->getEntityId()) {
            return $options[$winBidData->getShop()];
        }
        return $options[0];
    }

    /**
     * getStatusLabel
     * @param int $value of status
     * @return false| string
     */
    public function getStatusLabel($value)
    {
        if ($value != '') {
            $options = $this->productOptions->getOptionArray();
            return $options[$value];
        }
        return false;
    }

    /**
     * getBidDetailUrl
     * @param int $auctionId
     * @return false| string
     */
    public function getBidDetailUrl($auctionId)
    {
        return $this->getUrl(
            'mpauction/account/auctionbiddetail',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id'=>$auctionId
            ]
        );
    }

    /**
     * @return boolean
     */
    public function isAuctionEnable()
    {
        return $this->_scopeConfig->getValue('wk_mpauction/general_settings/enable');
    }
    
    /**
     * @return integer
     */
    public function getUtcOffset($date)
    {
        return timezone_offset_get(new \DateTimeZone($this->_localeDate->getConfigTimezone()), new \DateTime($date));
    }
    public function converToTz($dateTime = "")
    {
        $configZone = $this->localeDate->getConfigTimezone();
        $defaultZone = $this->localeDate->getDefaultTimezone();
        $toTz = $this->localeDate->getConfigTimezone();
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone('UTC'));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('m/d/Y H:i:s');
        return $dateTime;
    }
    /**
     * Get formatted by price and currency.
     *
     * @param   $price
     * @param   $currency
     *
     * @return array || float
     */
    public function getFormatedPrice($price, $currency)
    {
        return $this->_priceCurrency->format(
            $price,
            true,
            2,
            null,
            $currency
        );
    }
    public function getAllMpProducts()
    {
        $storeId = $this->mpHelperData->getCurrentStoreId();
        $websiteId = $this->mpHelperData->getWebsiteId();

        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if ($this->customerSession->getCustomerGroupId() == 4) {
            $customerId = $this->getCurrentCustomerId();
        }
        if (!$this->_productList) {
            $paramData = $this->request->getParams();
            $filter = '';
            $filterStatus = '';
            $filterDateFrom = '';
            $filterDateTo = '';
            $from = null;
            $to = null;
            if (isset($paramData['s'])) {
                $filter = $paramData['s'] != '' ? $this->escapeHtml($paramData['s']): '';
            }
            if (isset($paramData['status'])) {
                $filterStatus = $paramData['status'] != '' ? $paramData['status'] : '';
            }
            if (isset($paramData['from_date'])) {
                $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
            }
            if (isset($paramData['to_date'])) {
                $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
            }
            if ($filterDateTo) {
                $todate = date_create($filterDateTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if (!$to) {
                $to = date('Y-m-d 23:59:59');
            }
            if ($filterDateFrom) {
                $fromdate = date_create($filterDateFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            $proAttId = $this->entityAttribute->getIdByCode('catalog_product', 'name');
            $proStatusAttId = $this->entityAttribute->getIdByCode(
                'catalog_product',
                'status'
            );

            $catalogProductEntity = $this->collection->getTable('catalog_product_entity');

            $catalogProductEntityVarchar = $this->collection->getTable(
                'catalog_product_entity_varchar'
            );

            $catalogProductEntityInt = $this->collection->getTable(
                'catalog_product_entity_int'
            );

            /* Get Seller Product Collection for current Store Id */

            $storeCollection = $this->marketplaceProduct
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = '.$storeId.' AND
                cpev.value like "%'.$filter.'%" AND
                cpev.attribute_id = '.$proAttId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = '.$storeId.' AND
                cpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $storeCollection->getSelect()->where(
                    'cpei.value = '.$filterStatus
                );
            }

            $storeCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            if ($from && $to) {
                $storeCollection->getSelect()->where(
                    "cpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $storeCollection->getSelect()->group('mageproduct_id');

            $storeProductIDs = $storeCollection->getAllIds();

            /* Get Seller Product Collection for 0 Store Id */

            $adminStoreCollection = $this->marketplaceProduct->getCollection();

            if (count($storeCollection->getAllIds())) {
                $adminStoreCollection->addFieldToFilter(
                    'mageproduct_id',
                    ['nin' => $storeCollection->getAllIds()]
                );
            }
            $adminStoreCollection->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as acpev',
                'main_table.mageproduct_id = acpev.entity_id'
            )->where(
                'acpev.store_id = 0 AND
                acpev.value like "%'.$filter.'%" AND
                acpev.attribute_id = '.$proAttId
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityInt.' as acpei',
                'main_table.mageproduct_id = acpei.entity_id'
            )->where(
                'acpei.store_id = 0 AND
                acpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $adminStoreCollection->getSelect()->where(
                    'acpei.value = '.$filterStatus
                );
            }

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntity.' as acpe',
                'main_table.mageproduct_id = acpe.entity_id'
            );
            if ($from && $to) {
                $adminStoreCollection->getSelect()->where(
                    "acpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $adminStoreCollection->getSelect()->group('mageproduct_id');

            $adminProductIDs = $adminStoreCollection->getAllIds();

            $productIDs = array_merge($storeProductIDs, $adminProductIDs);

            $collection = $this->marketplaceProduct
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )
            ->addFieldToFilter(
                'mageproduct_id',
                ['in' => $productIDs]
            );

            $collection->setOrder('mageproduct_id');

            $this->_productlists = $collection;
        }
        return $this->_productlists;
    }
}
