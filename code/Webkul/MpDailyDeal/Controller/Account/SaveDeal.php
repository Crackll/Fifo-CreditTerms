<?php
namespace Webkul\MpDailyDeal\Controller\Account;

/**
 * Webkul_MpDailyDeals Deal save controller
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory as MarketplaceProductFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;

class SaveDeal extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    public $dirCurrencyFactory;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $localeDate;

    /**
     * @var MarketplaceProductFactory
     */
    public $marketplaceProductFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    public $formKeyValidator;

    /**
     * @param Context                                    $context
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory   $dirCurrencyFactory
     * @param ProductRepositoryInterface                 $productRepository
     * @param TimezoneInterface                          $localeDate,
     * @param MarketplaceProductFactory                  $mpProductFactory
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory,
        ProductRepositoryInterface $productRepository,
        TimezoneInterface $localeDate,
        MarketplaceProductFactory $mpProductFactory,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->dirCurrencyFactory = $dirCurrencyFactory;
        $this->productRepository = $productRepository;
        $this->localeDate = $localeDate;
        $this->marketplaceProductFactory = $mpProductFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->coreSession = $coreSession;
        parent::__construct($context);
    }

    /**
     * Deal save on product
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/deallist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        $varienObject = new \Magento\Framework\DataObject();
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        if ($data['deal_status'] && !is_numeric($data['deal_value'])) {
            $this->messageManager->addError(__('Deal Value has to be completed.'));
            return $resultRedirect->setUrl($this->_url->getUrl('mpdailydeal/account/deallist'));
        }
        if ($data['deal_status'] && !$data['deal_from_date']) {
            $this->messageManager->addError(__('Deal from date has to be completed.'));
            return $resultRedirect->setUrl($this->_url->getUrl('mpdailydeal/account/deallist'));
        }
        if ($data['deal_status'] && !$data['deal_to_date']) {
            $this->messageManager->addError(__('Deal to date has to be completed.'));
            return $resultRedirect->setUrl($this->_url->getUrl('mpdailydeal/account/deallist'));
        }
        
        if ($data && isset($data['id'])) {
            $mpProduct = $this->marketplaceProductFactory->create()->getCollection()
                              ->addFieldToFilter('mageproduct_id', $data['id'])
                              ->addFieldToFilter('seller_id', $this->customerSession->getCustomerId())
                              ->getFirstItem();
            if ($mpProduct->getEntityId()) {
                $product = $this->productRepository->getById($data['id'], true);
                $product->setStoreId(0);
                try {
                    if ($data['deal_value']<0) {
                        throw new LocalizedException(
                            __("Deal value should not be less than 0.")
                        );
                    }
                    if ($data['deal_discount_type']=="percent" && $data['deal_value']>100) {
                        throw new LocalizedException(
                            __("In case of percent type discount, the deal value should not be more than 100.")
                        );
                    } elseif ($data['deal_discount_type']=="fixed"
                            && $data['deal_value']>$product->getPrice()) {
                        throw new LocalizedException(
                            __("In case of fixed type discount, the deal value should not be more than product price.")
                        );
                    } elseif ($data['deal_discount_type']=="percent" && $data['deal_value']==100) {
                        throw new LocalizedException(
                            __("In case of Percent type discount, the deal value should not be 100 percent.")
                        );
                    } elseif ($data['deal_discount_type']=="fixed" && $data['deal_value']==$product->getPrice()) {
                        throw new LocalizedException(
                            __("In case of fixed type discount, the deal value should not be same with regular price.")
                        );
                    }

                } catch (LocalizedException $e) {
                    $varienObject->setData($data);
                    $this->coreSession->setMpProductDealData($varienObject);
                    $this->messageManager->addError(__($e->getMessage()));
                    return $resultRedirect->setUrl(
                        $this->_url->getUrl(
                            'mpdailydeal/account/adddeal',
                            ['id'=>$data['id']]
                        )
                    );
                }
                $this->coreSession->unsMpProductDealData();
                $price = $data['deal_value'];
                $discount = '';
                $data['deal_discount_percentage'] = '';
                if (is_numeric($price)) {
                    if ($data['deal_discount_type'] == 'percent') {
                        $price = $product->getPrice() * ($data['deal_value']/100);
                        $discount = $data['deal_value'];
                    } elseif ($product->getPrice() > 0) {
                        $data['deal_value'] = $this->converPriceInBaseCurrency($data['deal_value']);
                        $discount = ($data['deal_value']/$product->getPrice())*100;
                    }
                    $price = $this->converPriceInBaseCurrency($price);
                    $data['deal_discount_percentage'] = round(100-$discount);

                }
                //convert date time in Default Time Zone
                $data['deal_from_date'] = $this->converToTz(
                    $data['deal_from_date'],
                    $data['time_zone'],
                    $this->localeDate->getConfigTimezone()
                );
                $data['deal_to_date'] = $this->converToTz(
                    $data['deal_to_date'],
                    $data['time_zone'],
                    $this->localeDate->getConfigTimezone()
                );
                if ($product->getEntityId()) {
                    //To Do for default special price of magneto
                        $product->setSpecialPrice(null);
                        $product->getResource()->saveAttribute($product, 'special_price');
                        $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                        $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                    foreach ($data as $key => $value) {
                        $product->setData($key, $value);
                    }
                    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                    $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                    $product->setSpecialFromDateIsFormated(true);
                    $product->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Deal information saved successfully.'));
        return $resultRedirect->setUrl($this->_url->getUrl('mpdailydeal/account/deallist'));
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

    /**
     * convert $price from current currency to base currency
     * @param decimal $price which we want to convert
     */
    public function converPriceInBaseCurrency($price)
    {
        $store = $this->storeManager->getStore();
        $currencyModel = $this->dirCurrencyFactory->create();
        $baseCunyCode = $store->getBaseCurrencyCode();
        $cuntCunyCode = $store->getCurrentCurrencyCode();

        $allowedCurrencies = $currencyModel->getConfigAllowCurrencies();
        $rates = $currencyModel->getCurrencyRates($baseCunyCode, array_values($allowedCurrencies));

        $rates[$cuntCunyCode] = isset($rates[$cuntCunyCode]) ? $rates[$cuntCunyCode] : 1;
        return $price/$rates[$cuntCunyCode];
    }
}
