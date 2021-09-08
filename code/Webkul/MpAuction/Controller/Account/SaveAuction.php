<?php
namespace Webkul\MpAuction\Controller\Account;

/**
 * Webkul_MpAuction Auction save controller
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory as MarketplaceProductFactory;
use Webkul\MpAuction\Model\ProductFactory as MpAuctionProductFactory;
use Webkul\MpAuction\Helper\Data;

class SaveAuction extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $dirCurrencyFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var MarketplaceProductFactory
     */
    private $marketplaceProductFactory;

    /**
     * @var MpAuctionProductFactory
     */
    private $mpAuctionProductFactory;

    /**
     * @var Data
     */
    private $auctionHelper;
    /**
     * @param Context                                    $context
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory   $dirCurrencyFactory
     * @param ProductRepositoryInterface                 $productRepository
     * @param TimezoneInterface                          $localeDate,
     * @param MarketplaceProductFactory                  $mpProductFactory
     * @param MpAuctionProductFactory                    $mpAucProductFactory
     * @param Data                                       $auctionHelper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory,
        ProductRepositoryInterface $productRepository,
        TimezoneInterface $localeDate,
        MarketplaceProductFactory $mpProductFactory,
        MpAuctionProductFactory $mpAucProductFactory,
        Data $auctionHelper
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->dirCurrencyFactory = $dirCurrencyFactory;
        $this->productRepository = $productRepository;
        $this->localeDate = $localeDate;
        $this->marketplaceProductFactory = $mpProductFactory;
        $this->mpAuctionProductFactory = $mpAucProductFactory;
        $this->_auctionHelper = $auctionHelper;
        parent::__construct($context);
    }

    /**
     * Auction save on product
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        
        list($errors) = $this->validatePost($data);
        if (empty($errors)) {
            if ($data && isset($data['id'])) {
                $sellerId = $this->_auctionHelper->getCurrentCustomerId();
                $mpProduct = $this->marketplaceProductFactory->create()->getCollection()
                                ->addFieldToFilter('mageproduct_id', $data['id'])
                                ->addFieldToFilter('seller_id', $sellerId)->setPageSize(1)->getFirstItem();
                if ($mpProduct->getEntityId()) {
                    $getCurrentDateTime = $this->_auctionHelper->getCurrentDateTime();
                    
                    $product = $this->productRepository->getById($data['id'], true);

                    /* for increment price */
                    $increment = null;
                    if (isset($data['increment'])) {
                        foreach ($data['increment']['from'] as $key => $value) {
                            if ($data['increment']['from'][$key] > $data['increment']['to'][$key]) {
                                $temp = $data['increment']['from'][$key];
                                $data['increment']['from'][$key] = $data['increment']['to'][$key];
                                $data['increment']['to'][$key] = $temp;
                            }
                            if ($data['increment']['price'][$key] < 0 || $data['increment']['price'][$key] == '') {
                                $data['increment']['price'][$key] = 1;
                            }
                            $indexKey = $data['increment']['from'][$key].'-'.$data['increment']['to'][$key];
                            $increment[$indexKey] = $data['increment']['price'][$key];
                        }
                    }
                    $data['increment_price'] = json_encode($increment);
                    
                    $mpAucProduct = $this->mpAuctionProductFactory->create()->getCollection()
                                            ->addFieldToFilter('customer_id', $sellerId)
                                            ->addFieldToFilter('product_id', $data['id'])
                                            ->addFieldToFilter('auction_status', 1)->setPageSize(1)->getFirstItem();
                    if (isset($data['reserve_price']) && is_numeric($data['reserve_price'])) {
                        $data['reserve_price'] = floatval($data['reserve_price']);
                        $data['starting_price'] = $this->converPriceInBaseCurrency($data['starting_price']);
                        $data['reserve_price'] = $this->converPriceInBaseCurrency($data['reserve_price']);
                    } else {
                        $data['reserve_price'] = null;
                    }
                    if (!isset($data['min_amount'])) {
                        $data['min_amount'] = $data['starting_price'];
                    } else {
                        $data['min_amount'] = $this->converPriceInBaseCurrency($data['min_amount']);
                    }
                    $configZone = $this->_auctionHelper->getConfigTimeZone();
                    if (isset($data['aid']) && empty($data['aid'])) {
                        $data['start_auction_time'] = $this->
                        converToTz($data['start_auction_time'], $data['time_zone']);
                        $data['stop_auction_time'] = $this->
                        converToTz($data['stop_auction_time'], $data['time_zone']);
                    } else {
                        $data['start_auction_time'] = $mpAucProduct->getStartAuctionTime();
                        $data['stop_auction_time'] = $mpAucProduct->getStopAuctionTime();
                    }
                    
                    $data['product_id'] = $data['id'];
                    $data['customer_id'] = $sellerId;
                    $data['auction_status'] = 1;
                    if ($mpAucProduct->getEntityId()) {
                        if ($data['starting_price'] != $mpAucProduct->getStartingPrice()) {
                            $data['min_amount'] = $this->converPriceInBaseCurrency($data['starting_price']);
                        }
                        foreach ($data as $key => $value) {
                            $mpAucProduct->setData($key, $value);
                        }
                    } else {
                        $mpExpAucProducts = $this->mpAuctionProductFactory->create()->getCollection()
                                                    ->addFieldToFilter('customer_id', $sellerId)
                                                    ->addFieldToFilter('product_id', $data['id'])
                                                    ->addFieldToFilter('expired', 0);
                        
                        foreach ($mpExpAucProducts as $expProduct) {
                            $expProduct->setExpired(1);
                            $this->saveObj($expProduct);
                        }
                        $data['min_amount'] = $data['starting_price']; // after change status re-write min amount
                        
                        $mpAucProduct = $this->mpAuctionProductFactory->create();
                        $mpAucProduct->setData($data);
                    }
                    $this->saveObj($mpAucProduct);
                    $product->setAuctionType(implode(",", $data['auction_type']));
                    $this->saveObj($product);
                }
            }
            $this->messageManager->addSuccess(__('Auction information saved successfully.'));
            return $resultRedirect->setUrl($this->_url->getUrl('mpauction/account/auctionlist'));
        } else {
            foreach ($errors as $message) {
                $this->messageManager->addError($message);
            }
            return $resultRedirect->setUrl($this->_url->getUrl('mpauction/account/addauction', ['pid'=>$data['id']]));
        }
    }
    
    /**
     * convert Datetime from one zone to another
     * @param string $dateTime which we want to convert
     * @param string $fromTz timezone from which we want to convert
     * @param string $toTz timezone in which we want to convert
     */
    private function converToTz($dateTime = "", $fromTz = '', $toTz = '')
    {
        if (!$toTz) {
            $toTz = "UTC";
        }
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('m/d/Y H:i:s');
        return $dateTime;
    }

    /**
     * convert $price from current currency to base currency
     * @param decimal $price which we want to convert
     */
    private function converPriceInBaseCurrency($price)
    {
        $store = $this->storeManager->getStore();
        $currencyModel = $this->dirCurrencyFactory->create();
        $baseCunyCode = $store->getBaseCurrencyCode();
        $cuntCunyCode = $store->getCurrentCurrencyCode();

        $allowedCurrencies = $currencyModel->getConfigAllowCurrencies();
        $rates = $currencyModel->getCurrencyRates($baseCunyCode, array_values($allowedCurrencies));

        $rates[$cuntCunyCode] = isset($rates[$cuntCunyCode]) ? $rates[$cuntCunyCode] : 1;

        if (isset($rates[$cuntCunyCode]) && is_numeric($rates[$cuntCunyCode])) {
            return $price/$rates[$cuntCunyCode];
        }
        return $price;
    }

    /**
     * saveObj
     * @param Object
     * @return void
     */
    private function saveObj($object)
    {
        $object->save();
    }
    /**
     * @return array
     */
    private function validatePost(&$data)
    {
        $errors = [];
        foreach ($data as $code => $value) {
            switch ($code) {
                case 'auction_type':
                    if (empty($value)) {
                        $errors[] = __('Auction type has to selected.');
                    }
                    break;
                case 'starting_price':
                    if (!preg_match('/^([0-9])+?[0-9.]*$/', $value) || $value < 0) {
                        $errors[] = __('Starting price should contain only decimal numbers.');
                    }
                    break;
                case 'reserve_price':
                    if ($value!='') {
                        if ((!preg_match('/^([0-9])+?[0-9.]*$/', $value)
                        || $value < 0) && $this->_auctionHelper->getAuctionConfiguration()['reserve_enable']) {
                            $errors[] = __('Reserve price should contain only decimal numbers.');
                        }
                        if ($value < $data['starting_price']
                        && $this->_auctionHelper->getAuctionConfiguration()['reserve_enable']) {
                            $errors[] = __('Enter reserve price greater than starting price.');
                        }
                    }
                    break;
                case 'start_auction_time':
                    if (!preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $value) || !$value) {
                        $errors[] = __('Start auction time has to be completed.');
                    }
                    break;
                case 'stop_auction_time':
                    if (!preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $value) || !$value) {
                        $errors[] = __('Stop auction time has to be completed.');
                    }
                    break;
                case 'days':
                    if (!preg_match('/^([0-9])+?[0-9.]*$/', $value) || $value <= 0) {
                        $errors[] = __('Number of days till winner can buy should contain only numbers.');
                    }
                    break;
                case 'min_qty':
                    if (!preg_match('/^([0-9])+?[0-9.]*$/', $value) || !$value) {
                        $errors[] = __('Minimum qty should contain only numbers.');
                    }
                    break;
                case 'max_qty':
                    if (!preg_match('/^([0-9])+?[0-9.]*$/', $value) || !$value) {
                        $errors[] = __('Maximum qty should contain only numbers.');
                    }
                    if ($value < $data['min_qty']) {
                        $errors[] = __('Enter max quantity equal or greater than min quantity');
                    }
                    break;
            }
        }
      
        if (isset($data['aid']) && empty($data['aid'])) {
            $startAuctionTimeConvertTime = $this->_auctionHelper->getAuctionAddedCurrentTime(
                $this->converToTz($data["start_auction_time"], $data['time_zone'])
            );
            $stopAuctionTimeConvertTime = $this->_auctionHelper->getAuctionAddedCurrentTime(
                $this->converToTz($data["stop_auction_time"], $data['time_zone'])
            );
            //check date time must be greater than today datetime
            $getCurrentDateTime = $this->_auctionHelper->getCurrentDateTime();
        
            if ($getCurrentDateTime > $startAuctionTimeConvertTime
            || $getCurrentDateTime > $stopAuctionTimeConvertTime) {
                $errors[] = __('Start & stop auction time must be greater than today datetime.');
            }
        }
        
        if ((isset($data["start_auction_time"]) && isset($data["stop_auction_time"]))
         && ($data["start_auction_time"] > $data["stop_auction_time"])) {
            $errors[] = __('Stop auctin must be greater than start auction time.');
        }
        
        return [$errors];
    }
}
