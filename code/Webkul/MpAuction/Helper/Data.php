<?php

/**
 * Webkul_MpAuction data helper
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Webkul\Marketplace\Helper\Data as MpHelperData;
use Webkul\MpAuction\Model\ResourceModel\Product\CollectionFactory as AuctCollFactory;
use Webkul\MpAuction\Model\AmountFactory;
use Webkul\MpAuction\Model\AutoAuctionFactory;
use Webkul\MpAuction\Model\Product as AuctionProduct;
use Webkul\MpAuction\Model\IncrementalPriceFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Filesystem\Io\File;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EntityAttribute;
use Webkul\Marketplace\Model\Product as MarketplaceProduct;
use Webkul\Marketplace\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\RequestInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var File */
    private $file;

    /**
     * @var Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var fullPageCache
     */
    private $fullPageCache;
     
    /**
     * @var \Webkul\Auction\Model\Auction
     */
    private $auctionProduct;
     /**
      * @var \Magento\Framework\Pricing\PriceCurrencyInterface
      */
    private $_currency;
    /**
     * @var TimezoneInterface
     */
    private $localTimeZone;

    /**
     * @var MpHelperData
     */
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    private $mpHelperData;

    /**
     * @var AutoAuctionFactory
     */
    private $autoAuctionFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var EntityAttribute
     */
    private $entityAttribute;

    /**
     * @var MarketplaceProduct
     */
    private $marketplaceProduct;

    /**
     * @var AuctCollFactory
     */
    private $auctionProFactory;

    /**
     * @var IncrementalPriceFactory
     */
    private $incrementalPriceFactory;

    /**
     * @var AmountFactorye
     */
    private $aucAmountFactory;

    /**
     * @var product
     */
    private $product;
    /*
    * @var \Magento\Catalog\Model\ProductFactory
    */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_productlists;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param  \Magento\Catalog\Model\ProductFactory $productFactory,
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param TimezoneInterface                     $timezoneInterface
     * @param RequestInterface                      $request
     * @param EntityAttribute                       $entityAttribute
     * @param CustomerSession                       $customerSession
     * @param MpHelperData                          $mpHelperData
     * @param AuctCollFactory                       $auctionProFactory
     * @param AmountFactory                         $aucAmountFactory
     * @param AutoAuctionFactory                    $autoAuctionFactory
     * @param Collection                            $collection
     * @param MarketplaceProduct                    $marketplaceProduct
     * @param IncrementalPriceFactory               $incPriceFactory
     * @param MagentoStoreModelStoreManagerInterface      $storeManager
     * @param File                                   $file
     */
    public function __construct(
        \Magento\Directory\Model\Currency $currency,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        RequestInterface $request,
        DirectoryList $directoryList,
        AuctionProduct $auctionProduct,
        Collection $collection,
        TimezoneInterface $timezoneInterface,
        CustomerSession $customerSession,
        PriceHelper $priceHelper,
        MpHelperData $mpHelperData,
        AuctCollFactory $auctionProFactory,
        AmountFactory $aucAmountFactory,
        MarketplaceProduct $marketplaceProduct,
        EntityAttribute $entityAttribute,
        AutoAuctionFactory $autoAuctionFactory,
        IncrementalPriceFactory $incPriceFactory,
        TimezoneInterface $localeDate,
        Product $product,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\Escaper $_escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        File $file
    ) {
        $this->_currency = $currency;
        $this->date = $date;
        $this->_productFactory = $productFactory;
        $this->_directoryHelper = $directoryHelper;
        $this->mpHelperData = $mpHelperData;
        $this->collection = $collection;
        $this->request         = $request;
        $this->auctionProduct = $auctionProduct;
        $this->auctionProFactory = $auctionProFactory;
        $this->customerSession = $customerSession;
        $this->priceHelper = $priceHelper;
        $this->localTimeZone = $timezoneInterface;
        $this->aucAmountFactory = $aucAmountFactory;
        $this->autoAuctionFactory = $autoAuctionFactory;
        $this->incrementalPriceFactory = $incPriceFactory;
        $this->product = $product;
        $this->entityAttribute = $entityAttribute;
        $this->marketplaceProduct = $marketplaceProduct;
        $this->directoryList    = $directoryList;
        $this->localeDate = $localeDate;
        $this->escapeHtml=$_escaper;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->_storeManager = $storeManager;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * return current date time
     */
    public function getCurrentDateTime()
    {
        return  $this->date->gmtDate();
    }
    /**
     * getAdded DateTime
     *
     * @return string added datetime
     */
    public function getAuctionAddedCurrentTime($dateTime)
    {
        $minutes_to_add = 2;

        $time = new \DateTime($dateTime);
        $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
        $dateTime = $time->format('Y-m-d H:i:s');
        return $dateTime;
    }

     /**
      * Get Configuration Detail of Auction
      * @return array of Auction Configuration Detail
      */
   
    public function getAuctionConfiguration()
    {
        $storeScope=\Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $auctionConfig=[
            'enable' => $this->scopeConfig->getValue('wk_mpauction/general_settings/enable'),
            'auction_rule' => $this->scopeConfig->getValue('wk_mpauction/general_settings/auction_rule'),
            'show_bidder' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_bidder'),
            'show_price' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_price'),
            'reserve_enable' => $this->scopeConfig->getValue('wk_mpauction/reserve_option/enable'),
            'reserve_price' => $this->scopeConfig->getValue('wk_mpauction/reserve_option/price'),
            'show_curt_auc_price' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_curt_auc_price'),
            'show_auc_detail' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_auc_detail'),
            'auto_enable' => $this->scopeConfig->getValue('wk_mpauction/auto/enable'),
            'auto_auc_limit' => $this->scopeConfig->getValue('wk_mpauction/auto/limit'),
            'show_auto_details' => $this->scopeConfig->getValue('wk_mpauction/auto/show_auto_details'),
            'auto_use_increment' => $this->scopeConfig->getValue('wk_mpauction/auto/use_increment'),
            'show_autobidder_name' => $this->scopeConfig->getValue('wk_mpauction/auto/show_autobidder_name'),
            'show_auto_bid_amount' => $this->scopeConfig->getValue('wk_mpauction/auto/show_bid_amount'),
            'show_auto_outbid_msg' => $this->scopeConfig->getValue(
                'wk_mpauction/auto/show_auto_outbid_msg',
                $storeScope
            ),
            'enable_auto_outbid_msg' => $this->scopeConfig->getValue(
                'wk_mpauction/auto/enable_auto_outbid_msg'
            ),
            'show_winner_msg' => $this->scopeConfig->getValue(
                'wk_mpauction/general_settings/show_winner_msg',
                $storeScope
            ),
            'increment_auc_enable' => $this->scopeConfig->getValue('wk_mpauction/increment_option/enable', $storeScope),
            'enable_admin_email' => $this->scopeConfig->getValue('wk_mpauction/emails/enable_admin_email'),
            'admin_notify_email_template' => $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/admin_notify_email_template'),
            'enable_seller_email' => $this->scopeConfig->getValue('wk_mpauction/emails/enable_seller_email'),
            'seller_notify_email_template' => $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/seller_notify_email_template'),
            'enable_outbid_email' => $this->scopeConfig->getValue('wk_mpauction/emails/enable_outbid_email'),
            'outbid_notify_email_template' => $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/outbid_notify_email_template'),
            'enable_winner_notify_email'  =>  $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/enable_winner_notify_email'),
            'winner_notify_email_template' => $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/winner_notify_email_template'),
            'admin_email_address' => $this->scopeConfig->getValue('wk_mpauction/emails/admin_email_address'),
            'enable_submit_bid_email' => $this->scopeConfig->getValue('wk_mpauction/emails/enable_submit_bid_email'),
            'bidder_notify_email_template' => $this->scopeConfig
                ->getValue('wk_mpauction/emails/bidder_notify_email_template'),
            'autobidder_notify_email_template' => $this->scopeConfig
                ->getValue('wk_mpauction/emails/autobidder_notify_email_template')
        ];
        return $auctionConfig;
    }

    /**
     * @param object $product
     * @return html string
     */
    public function getProductAuctionDetail($product)
    {
        $product = $this->product->load($product->getEntityId());
        $modEnable = $this->scopeConfig->getValue('wk_mpauction/general_settings/enable');
        $content = "";
        $htmlDataAttr = "";
        $proByItNow = 0;
        if ($modEnable) {
            $auctionOpt = $product->getAuctionType();
            $auctionOpt = explode(',', $auctionOpt);
            $proByItNow = in_array(1, $auctionOpt) !== false ? 1:0;
            $auctionData = $this->auctionProFactory->create()
                    ->addFieldToFilter('product_id', $product->getEntityId())
                    ->addFieldToFilter('expired', 0)
                    //->addFieldToFilter('auction_status', ['in' => [1,0]])
                    ->setPageSize(1);
            $auctionData = $this->getFirstItemFromRecord($auctionData);
            $clock = "";
            $content = "";
            if ($auctionData) {
                $currentAuctionPriceData = $this->aucAmountFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['eq' => $product->getEntityId()])
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionData['entity_id']])
                    ->setOrder('auction_amount', 'DESC')
                    ->getFirstItem();

                if ($currentAuctionPriceData->getAuctionAmount()) {
                    $highestBidAmount = $currentAuctionPriceData->getAuctionAmount();
                    $highestamount = $this->priceHelper
                        ->currency($currentAuctionPriceData->getAuctionAmount(), true, false);
                } else {
                    $highestBidAmount = 0;
                    $highestamount = $this->priceHelper->currency(0.00, true, false);
                }
                $auctionData = $auctionData->getData();
                $today = $this->localTimeZone->date()->format('m/d/y H:i:s');
                $auctionData['start_auction_time']= $this->
                converToTz($auctionData['start_auction_time']);
                $auctionData['stop_auction_time']= $this->
                converToTz($auctionData['stop_auction_time']);
                $startAuctionTime = date_format(date_create($auctionData['start_auction_time']), 'Y-m-d H:i:s');
                $stopAuctionTime = date_format(date_create($auctionData['stop_auction_time']), 'Y-m-d H:i:s');
                $difference = strtotime($stopAuctionTime) - strtotime($today);
                if ($difference > 0 && strtotime($startAuctionTime) < strtotime($today)) {
                    $clock = '<p class="wk_cat_count_clock" data-stoptime="'.$auctionData['stop_auction_time']
                                .'" data-diff_timestamp ="'.$difference.'" data-highest-bid="'.
                                $highestamount.'" data-highest-bid-amount="'.
                                $highestBidAmount.'" data-startflag="0" data-open-bid-amount="'.
                                $this->priceHelper->currency($auctionData['starting_price'], true, false).'"></p>';
                } elseif (strtotime($startAuctionTime) > strtotime($today)) {
                    $difference = strtotime($startAuctionTime) - strtotime($today);
                    $clock = '<p class="wk_cat_count_clock" data-stoptime="'.$auctionData['stop_auction_time']
                    .'" data-diff_timestamp ="'.$difference.'" data-highest-bid="'.
                    $highestamount.'" data-highest-bid-amount="'.
                    $highestBidAmount.'" data-startflag="1" data-open-bid-amount="'.
                    $this->priceHelper->currency($auctionData['starting_price'], true, false).'"></p>';
                }

                $winnerBidDetail = $this->getWinnerBidDetail($auctionData['entity_id']);
                if ($winnerBidDetail && $this->customerSession->isLoggedIn()) {
                    $winnerCustomerId = $winnerBidDetail->getCustomerId();
                    $currentCustomerId = $this->getCurrentCustomerId();
                    if ($currentCustomerId == $winnerCustomerId) {
                        $price = $winnerBidDetail->getBidType() == 'normal' ? $winnerBidDetail->getAuctionAmount():
                                                                $winnerBidDetail->getWinningPrice();
                        $formatedPrice = $this->priceHelper->currency($price, true, false);
                        $shop = $winnerBidDetail->getShop();
                        $htmlDataAttr = 'data-winner="'.$shop.'" data-winning-amt="'.$formatedPrice.'"';
                    }
                }
                if (!$this->getAuctionConfiguration()['reserve_enable'] &&
                    $currentAuctionPriceData->getEntityId()) {
                    $proByItNow = 0;
                } elseif ($this->getAuctionConfiguration()['reserve_enable'] &&
                $currentAuctionPriceData->getEntityId() &&
                $auctionData['reserve_price'] <= $currentAuctionPriceData->getAuctionAmount()) {
                    $proByItNow = 0;
                }

                /**
                 * 2 : use for auction
                 * 1 : use for Buy it now
                 */
                
                if (in_array(2, $auctionOpt) && $proByItNow) {
                    $content = '<div class="auction buy-it-now" '.$htmlDataAttr.'>'.$clock.'</div>';
                } elseif (in_array(2, $auctionOpt)) {
                    $content = '<div class="auction" '.$htmlDataAttr.'>'.$clock.'</div>';
                } elseif ($proByItNow) {
                    $content = '<div class="buy-it-now"></div>';
                }
            }
        }
        return $content;
    }

    public function printLog($data, $flag = 1, $filename = "mpauction.log")
    {
        if ($flag == 1) {
            $path   = $this->directoryList->getPath("var");
            $logger = new \Zend\Log\Logger();
            if (!$this->file->fileExists($path."/log/")) {
                $this->file->mkdir($path."/log/", 0777, true);
            }
            $logger->addWriter(new \Zend\Log\Writer\Stream($path."/log/".$filename));
            if (is_array($data) || is_object($data)) {
                $data = $data;
            }
            $logger->info(var_export($data, true));
        }
    }

    /**
     * $auctionId
     * @param int $auctionId auction product id
     * @return AmountFactory || AutoAuctionFactory
     */
    public function getWinnerBidDetail($auctionId)
    {
        $aucAmtData = $this->aucAmountFactory->create()->getCollection()
                                                ->addFieldToFilter('auction_id', $auctionId)
                                                ->addFieldToFilter('winning_status', 1)
                                                ->addFieldToFilter('status', 0)->setPageSize(1);
        $aucAmtData = $this->getFirstItemFromRecord($aucAmtData);
        if ($aucAmtData && $aucAmtData->getEntityId()) {
            $aucAmtData->setBidType('normal');
            return $aucAmtData;
        } else {
            $aucAmtData = $this->autoAuctionFactory->create()->getCollection()
                                        ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                                        ->addFieldToFilter('flag', ['eq'=>1])
                                        ->addFieldToFilter('status', ['eq'=>0])->setPageSize(1);
            $aucAmtData = $this->getFirstItemFromRecord($aucAmtData);
            if ($aucAmtData && $aucAmtData->getEntityId()) {
                $aucAmtData->setBidType('auto');
                return $aucAmtData;
            }
        }
        return false;
    }

    /**
     * get incremental price value
     * @param Webkul\MpAuction\Model\Product $aucDetail
     * @param float $minPrice
     * @return false|float
     */
    public function getIncrementPriceAsRange($aucDetail, $minPrice = false)
    {
        $productInfo = $this->mpHelperData->getSellerProductDataByProductId($aucDetail->getProductId())
                                                ->setPageSize(1);
        $productInfoItem = $this->getFirstItemFromRecord($productInfo);
        $incPriceRang = false;
        
        if ($productInfo->getSize() && $productInfoItem->getSellerId() && 'null' !== $aucDetail->getIncrementPrice()) {
            $incPriceRang = $aucDetail->getIncrementPrice();
        } else {
            $incPriceRang = $this->incrementalPriceFactory->create()->getCollection()->setPageSize(1);
            $incPriceRang = $this->getFirstItemFromRecord($incPriceRang);
            
            $incPriceRang = $incPriceRang ? $incPriceRang->getIncval() : false;
        }
        $minAmount = $minPrice ? $minPrice : $aucDetail->getMinAmount();
        if ($incPriceRang) {
            $incPriceRang = json_decode($incPriceRang, true);
            if (is_array($incPriceRang)) {
                foreach ($incPriceRang as $range => $value) {
                    $range = explode('-', $range);
                    if ($minAmount >= $range[0] && $minAmount <= $range[1]) {
                        return floatval($value);
                    }
                }
            }
        }
        return false;
    }

    /**
     * get MpActive Auction Id
     * @param $productId int
     * @return int|false
     */
    public function getActiveMpAuctionId($productId)
    {
        $auctionData = $this->auctionProFactory->create()->addFieldToFilter('product_id', ['eq'=>$productId])
                                                ->addFieldToFilter('status', ['eq'=>0])
                                                ->addFieldToFilter('expired', ['eq'=>0])
                                                ->setOrder('entity_id')->setPageSize(1);
        $auctionData = $this->getFirstItemFromRecord($auctionData);

        return $auctionData ? $auctionData->getEntityId() : false;
    }

    /**
     * getFirstItemFromRecord
     * @param Collection Object
     * @return false | data
     */
    private function getFirstItemFromRecord($collection)
    {
        $row = false;
        foreach ($collection as $row) {
            $row =  $row;
        }
        return $row;
    }

    /**
     * get bid enable value
     *
     * @return boolean
     */
    public function isAuctionEnable()
    {
        return $this->scopeConfig->getValue('wk_mpauction/general_settings/enable');
    }
    public function checkAuctionAvail($aucId)
    {
        return $this->auctionProduct->load($aucId)->getData();
    }

    /**
     * @var $productList Product list of current page
     * @return array of current category product in auction and buy it now
     */
    public function getAuctionDetail($currentProId = false)
    {
        $auctionConfig = $this->getAuctionConfiguration();
        $auctionData = false;
        if ($auctionConfig['enable']) {
            if ($currentProId) {
                $curPro = $this->product->load($currentProId);
            } else {
                $auctionId = $this->getRequest()->getParam('id');
                $currentProId = $this->getAuctionProId($auctionId);
                $curPro = $this->product->load($currentProId);
            }

            $auctionOpt = $curPro->getAuctionType();
            $auctionOpt = explode(',', $auctionOpt);
            /**
             * 2 : use for auction
             * 1 : use for Buy it now
             */
            if (in_array(2, $auctionOpt)) {
                $auctionDataobj = $this->auctionProFactory->create()
                                        ->addFieldToFilter('product_id', ['eq'=>$currentProId])
                                        ->addFieldToFilter('auction_status', ['in' => [0,1]])
                                        ->addFieldToFilter('status', ['eq'=>0])->getFirstItem();
                $auctionData = $auctionDataobj->getData();

                if (isset($auctionData['entity_id'])) {
                    if ($auctionData['increment_opt'] && $auctionConfig['increment_auc_enable']) {
                        $incVal = $this->getIncrementPriceAsRange($auctionDataobj, $auctionData['min_amount']);
                        $auctionData['min_amount'] = $incVal ? $auctionData['min_amount'] + $incVal
                                                                            : $auctionData['min_amount'];
                    }
                    
                    $aucAmtData = $this->aucAmountFactory->create()->getCollection()
                                            ->addFieldToFilter('product_id', ['eq' => $currentProId])
                                            ->addFieldToFilter('auction_id', ['eq'=> $auctionData['entity_id']])
                                            ->addFieldToFilter('winning_status', ['eq'=>1])
                                            ->addFieldToFilter('shop', ['neq'=>0])->getFirstItem();

                    if ($aucAmtData->getEntityId()) {
                        $aucAmtData = $this->autoAuctionFactory->create()->getCollection()
                                                ->addFieldToFilter('auction_id', ['eq'=> $auctionData['entity_id']])
                                                ->addFieldToFilter('flag', ['eq'=>1])
                                                ->addFieldToFilter('shop', ['neq'=>0])->getFirstItem();
                    }

                    $today = $this->localTimeZone->date()->format('m/d/y H:i:s');
                    $auctionData['start_auction_time']= $this->
                    converToTz($auctionData['start_auction_time']);
                    $auctionData['stop_auction_time']= $this->
                    converToTz($auctionData['stop_auction_time']);
                    $auctionData['stop_auction_time'] = date_format(
                        date_create($auctionData['stop_auction_time']),
                        'Y-m-d H:i:s'
                    );
                    $auctionData['start_auction_time'] = date_format(
                        date_create($auctionData['start_auction_time']),
                        'Y-m-d H:i:s'
                    );
                    $auctionData['current_time_stamp'] = strtotime($today);
                    $auctionData['start_auction_time_stamp'] = strtotime($auctionData['start_auction_time']);
                    $auctionData['stop_auction_time_stamp'] = strtotime($auctionData['stop_auction_time']);
                    $auctionData['new_auction_start'] = $aucAmtData->getEntityId() ? true : false;
                    $auctionData['auction_title'] = __('Bid on ').$curPro->getName();
                    $auctionData['pro_url'] = $this->_urlBuilder->getUrl().$curPro->getUrlKey().'.html';
                    $auctionData['pro_name'] = $curPro->getName();
                    $auctionData['pro_buy_it_now'] = in_array(1, $auctionOpt) !== false ? 1:0;
                    $auctionData['pro_auction'] = in_array(2, $auctionOpt) !== false ? 1:0;
                    if ($auctionData['min_amount'] < $auctionData['starting_price']) {
                        $auctionData['min_amount'] = $auctionData['starting_price'];
                    }
                } else {
                    $auctionData = false;
                }
            }
        }
        return $auctionData;
    }
    public function getNormalBidAmountDataByCustomerId($customerId, $productId, $auctionId)
    {
        return $this->aucAmountFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['eq' => $productId])
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                    ->addFieldToFilter('customer_id', ['eq'=> $customerId])
                    ->setOrder('auction_amount', 'DESC')
                    ->getFirstItem();
    }
    public function getAutomaticBidAmountDataByCustomerId($customerId, $productId, $auctionId)
    {
        return $this->autoAuctionFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['eq' => $productId])
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                    ->addFieldToFilter('customer_id', ['eq'=> $customerId])
                    ->setOrder('amount', 'DESC')
                    ->getFirstItem();
    }
    public function checkByItOptionStatus($proId, $auctionId, $reserveAmount)
    {
        $auctionType = $this->product->load($proId)->getAuctionType();
        $types = explode(',', $auctionType);
        if (!in_array(1, $types)) {
            return false;
        }
        $reserveAuctionStatus = $this->getAuctionConfiguration()['reserve_enable'];
        if ($reserveAuctionStatus) {
            $auctionData = $this->aucAmountFactory->create()->getCollection()
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                    ->setOrder('auction_amount', 'DESC');
            foreach ($auctionData as $amount) {
                if ($amount->getAuctionAmount() >= $reserveAmount) {
                    return false;
                }
            }
        } else {
            $amountData = $this->aucAmountFactory->create()->getCollection()
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId]);
            if ($amountData->getSize()) {
                return false;
            }
        }
        return true;
    }

    public function getCurrentCustomerId()
    {
        return $this->mpHelperData->getCustomerId();
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
        if (!$this->_productlists) {
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

    /**
     * getProAuctionType
     * @return string type of auction
     */
    public function getProAuctionType($curPro)
    {
        $auctionType = "";
        if ($curPro) {
            $auctionOpt = explode(',', $curPro->getAuctionType());
            if ((in_array(2, $auctionOpt) && in_array(1, $auctionOpt)) || in_array(1, $auctionOpt)) {
                $auctionType = 'buy-it-now';
            } elseif (in_array(2, $auctionOpt)) {
                $auctionType = 'auction';
            }
        }
        return $auctionType;
    }
    public function getConfigTimeZone()
    {
        return $this->localeDate->getConfigTimezone();
    }

    public function getDefaultTimeZone()
    {
        return $this->localeDate->getDefaultTimezone();
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

    private function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\PageCache\Model\Cache\Type::class
            );
        }
        return $this->fullPageCache;
    }

    public function cleanByTags($productId)
    {
        $tags = ['CAT_P_'.$productId];
        $this->getCache()->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
    }
    public function cacheFlush()
    {
        $types = ['block_html','full_page'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

     // return wallet amount is enabled or not
    public function getWalletenabled()
    {
        return  $this->scopeConfig->getValue(
            'wk_auction/auction_wallet/enable_wallet'
        );
    }
 
     // return customer id from customer session
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
 
     // return currency currency code
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
 
    // return maximum amount set in system config
    public function getMaximumAmount()
    {
        return  $this->scopeConfig->getValue(
            'wk_auction/auction_wallet/maximumamounttoadd',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
     // return minimum amount set in system config
    public function getMinimumAmount()
    {
        return  $this->scopeConfig->getValue(
            'wk_auction/auction_wallet/minimumamounttoadd',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

      // convert currency amount
    public function getwkconvertCurrency($fromCurrency, $toCurrency, $amount)
    {
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$fromCurrency])) {
            $rates[$fromCurrency] = 1;
        }

        if ($baseCurrencyCode==$toCurrency) {
            $currencyAmount = $amount/$rates[$fromCurrency];
        } else {
            $amount = $amount/$rates[$fromCurrency];
            $currencyAmount = $this->convertCurrency($amount, $baseCurrencyCode, $toCurrency);
        }
        return $currencyAmount;
    }

    // get base currency code
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }
     // get all allowed currency in system config
    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }
     
    // get currency rates
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    // convert amount according to currenct currency
    public function convertCurrency($amount, $from, $to)
    {
        $finalAmount = $this->_directoryHelper
            ->currencyConvert($amount, $from, $to);

        return $finalAmount;
    }
}
