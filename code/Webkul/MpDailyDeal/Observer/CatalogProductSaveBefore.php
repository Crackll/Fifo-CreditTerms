<?php
/**
 * Webkul MpDailyDeal CatalogProductSaveBefore Observer.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CatalogProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $localeDate;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @param TimezoneInterface $localeDate,
     * @param ProductRepositoryInterface $productRepository,
     * @param RequestInterface $request,
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        ScopeConfigInterface $scopeInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->scopeConfig = $scopeInterface;
        $this->productFactory = $productFactory;
    }

    /**
     * product save event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $productData = $this->request->getParam('product');
        $modEnable = $this->scopeConfig->getValue('mpdailydeals/general/enable');
        if ($product->getDealStatus() && $modEnable && !empty($productData)) {
            if ($productData['deal_value']<0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Deal value should not be less than 0.")
                );
            }
            if ($productData['deal_discount_type']=="percent" && $productData['deal_value']>100) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("In case of percent type discount, the deal value should not be more than 100.")
                );
            } elseif ($productData['deal_discount_type']=="fixed"
                        && $productData['deal_value']>$productData['price']) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("In case of fixed type discount, the deal value should not be more than product price.")
                );
            } elseif ($productData['deal_discount_type']=="fixed" &&
            $productData['deal_value']==$productData['price']
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("In case of fixed type discount, the deal value should not be same with regular price.")
                );
            } elseif ($productData['deal_discount_type']=="percent" &&
            $productData['deal_value']==100
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("In case of Percent type discount, the deal value should not be 100 percent.")
                );
            }
            $configTimeZone = $this->localeDate->getConfigTimezone();
            $defaultTimeZone = $this->localeDate->getDefaultTimezone();
    
            $dealToDate = $productData['deal_to_date_tmp'];
            $dealFromDate = $productData['deal_from_date_tmp'];
            $dealToDate = $dealToDate == '' ? $this->converToTz(
                $productData['deal_to_date'],
                $configTimeZone,
                $defaultTimeZone
            ) : $dealToDate;
            $dealFromDate = $dealFromDate == '' ? $this->converToTz(
                $productData['deal_from_date'],
                $configTimeZone,
                $defaultTimeZone
            ) : $dealFromDate;
    
            if ($dealToDate != '' && $dealFromDate != '') {
                $product->setDealFromDate($dealFromDate);
                $product->setDealToDate($dealToDate);
            }
        } elseif ($product->getEntityId() && $modEnable) {
            // $proDealStatus = $this->productRepository->getById($product->getEntityId())->getDealStatus();
            $proDealStatus = $this->productFactory->create()->load($product->getEntityId())->getDealStatus();
            //To Do for default special price of magneto
            if ($proDealStatus) {
                $product->getResource()->saveAttribute($product, 'special_price');
                $product->setSpecialToDate('');
                $product->setSpecialFromDate('');
                $product->setSpecialPrice(null);
                $product->setDealDiscountPercentage('');
            } else {
                $product->setDealToDate(date("m/d/Y", strtotime('-1 day')));
                $product->setDealFromDate(date("m/d/Y", strtotime('-2 day')));
            }
        }
        if (!$product->getDealStatus()) {
            $product->setDealToDate(date("m/d/Y", strtotime('-1 day')));
            $product->setDealFromDate(date("m/d/Y", strtotime('-2 day')));
        }
        return $this;
    }

    /**
     * convert Datetime from one zone to another
     * @param string $dateTime which we want to convert
     * @param string $fromTz timezone from which we want to convert
     * @param string $toTz timezone in which we want to convert
     */
    public function converToTz($dateTime = "", $fromTz = '', $toTz = '')
    {
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone('UTC'));
        $dateTime = $date->format('m/d/Y H:i:s');
        return $dateTime;
    }
}
