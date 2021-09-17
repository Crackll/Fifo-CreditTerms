<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPromotionCampaign\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;
use Magento\Catalog\Api\SpecialPriceInterface;
use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public static $runningStatus = [
        0=>'<span style="display:inline-block;width: 10px;height: 10px;
            background:orange;border-radius: 50%;margin-right: 5px;"></span>',
        1=>'<span style="display:inline-block;width: 10px;height: 10px;
            background:green;border-radius: 50%;margin-right: 5px;"></span>',
        2=>'<span style="display:inline-block;width: 10px;height: 10px;
            background:red;border-radius: 50%;margin-right: 5px;"></span>'
    ];

    /**
     * TimeZone
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $timezone;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var SpecialPriceInterface
     */
    private $specialPrice;
 
    /**
     * @var SpecialPriceInterfaceFactory
     */
    private $specialPriceFactory;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $store,
     * @param ProductRepositoryInterface $productRepository,
     * @param \Magento\Framework\App\Helper\Context $context,
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
     * @param \Magento\Framework\Stdlib\BooleanUtils $booleanUtils
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Webkul\MpPromotionCampaign\Model\CampaignJoinFactory $campaignJoin,
     * @param \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaign,
     * @param \Psr\Log\LoggerInterface $logger,
     * @param \Magento\Framework\App\ResourceConnection $resource,
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper,
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider,
     * @param \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory $campaignProduct,
     * @param \Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\Grid\CollectionFactory $campaignCollection
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $store,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Stdlib\BooleanUtils $booleanUtils,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpPromotionCampaign\Model\CampaignJoinFactory $campaignJoin,
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaign,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory $campaignProduct,
        \Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\Grid\CollectionFactory $campaignCollection,
        SpecialPriceInterface $specialPrice,
        SpecialPriceInterfaceFactory $specialPriceFactory
    ) {
        $this->_resource = $resource;
        $this->filterProvider = $filterProvider;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->store = $store;
        $this->campaignProduct = $campaignProduct;
        $this->campaignCollection = $campaignCollection;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->campaign = $campaign;
        $this->campaignJoin = $campaignJoin;
        $this->storeManager =  $storeManager;
        $this->timezone = $timezone;
        $this->booleanUtils = $booleanUtils;
        $this->cacheTypeList = $cacheTypeList;
        $this->specialPrice = $specialPrice;
        $this->specialPriceFactory = $specialPriceFactory;
        parent::__construct($context);
    }
    public function getCMSContent($content)
    {
        $parsedContent = $this->filterProvider->getPageFilter()->filter($content);
        return $parsedContent;
    }

    /**
     * Media Directory path
     *
     * @return string
     */
    public function getMediaDirectory()
    {
        return $this->store->getStore()
        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    
    public function totalSellerCampaign()
    {
        $sellerId = $this->marketplaceHelper->getCustomerId();
        $data = [];
        $joinTable = $this->_resource->getTableName('mppromotionseller_campaign');
        $date = $this->getCurrentDateTime();
        $currentDateTime = $this->getDefaultZoneDateTime($date);
        $currentTimeStamp = $currentDateTime;

        // get seller's all campaign count
        $campaignAll = $this->campaign->create()->getCollection();
        $campaignAll->getSelect()->join(
            $joinTable.' as sellerCam',
            'main_table.entity_id = sellerCam.campaign_id'
        );
        $campaignAll->addFieldToFilter('sellerCam.seller_id', $sellerId);
        $campaignAll->addFieldToFilter('main_table.status', CampaignModel::STATUS_ENABLED);
        $data['allCampaign'] = $campaignAll->getSize();

        // get seller's all upcoming campaign count
        $campaignAll = $this->campaign->create()->getCollection();
        $campaignAll->getSelect()->join(
            $joinTable.' as sellerCam',
            'main_table.entity_id = sellerCam.campaign_id'
        );
        $campaignAll->addFieldToFilter('main_table.start_date', ['gt' => $currentTimeStamp]);
        $campaignAll->addFieldToFilter('main_table.end_date', ['gt' => $currentTimeStamp]);
        $campaignAll->addFieldToFilter('sellerCam.seller_id', $sellerId);
        $campaignAll->addFieldToFilter('main_table.status', 1);
        $data['upcommingCampaign'] = $campaignAll->getSize();

        // get seller's all live campaign count
        $campaignStatus = $this->campaign->create()->getCollection();
        $campaignStatus->getSelect()->join(
            $joinTable.' as sellerCam',
            'main_table.entity_id = sellerCam.campaign_id'
        );
        $campaignStatus->addFieldToFilter('sellerCam.seller_id', $sellerId);
        $campaignStatus->addFieldToFilter('main_table.start_date', ['lt' => $currentTimeStamp]);
        $campaignStatus->addFieldToFilter('main_table.end_date', ['gt' => $currentTimeStamp]);
        $campaignStatus->addFieldToFilter('main_table.status', CampaignModel::STATUS_ENABLED);
        $data['liveCampaign'] = $campaignStatus->getSize();

        // get seller's all finished campaign count
        $campaignData = $this->campaign->create()->getCollection();
        $campaignData->getSelect()->join(
            $joinTable.' as sellerCam',
            'main_table.entity_id = sellerCam.campaign_id'
        );
        $campaignData->addFieldToFilter('sellerCam.seller_id', $sellerId);
        $campaignData->addFieldToFilter('main_table.end_date', ['lt' => $currentTimeStamp]);
        $campaignAll->addFieldToFilter('main_table.status', CampaignModel::STATUS_ENABLED);
        $data['finishCampaign'] = $campaignData->getSize();
        return $data;
    }

    /**
     * Campaign Id from URL
     *
     * @param string $url
     * @return int
     */
    public function getCampaignIdFromUrl($url)
    {
        $i=0;
        $urlArr = explode('/', $url);
        $urlArrCount = count($urlArr);
        for ($i=0; $i<$urlArrCount; $i++) {
            if ($urlArr[$i] == "id") {
                $i++;
                break;
            }
        }
        if ($i == $urlArrCount) {
            return false;
        }
        return $urlArr[$i];
    }

    /**
     * Current Date Time in locale timezone
     *
     * @return datetime
     */
    public function getCurrentDateTime()
    {
        return $this->timezone->date()->format('Y-m-d H:i:s');
    }

    /**
     * getDefaultZoneDateTime as magento saved dates in default utc timezone
     *
     * @param datetime $date
     * @return datetime
     */
    public function getDefaultZoneDateTime($date)
    {
        $timezoneInterface = $this->timezone;
        $configTimezone = $timezoneInterface->getConfigTimezone();
        $defaultTimezone = $timezoneInterface->getDefaultTimezone();
        $dateStart = new \DateTime(
            $date,
            new \DateTimeZone($configTimezone)
        );
        $dateStart->setTimezone(new \DateTimeZone($defaultTimezone));
        return $dateStart->format('Y-m-d H:i:s');
    }

    /**
     * getLocaleZoneDateTime as magento saved dates in locale timezone
     *
     * @param datetime $date
     * @return datetime
     */
    public function getLocaleZoneDateTime($date)
    {
        if (isset($date) && $date !== "0000-00-00 00:00:00") {
            $date = $this->timezone->date(new \DateTime($date));
            $configTimezone = $this->timezone->getConfigTimezone();
            $timezone = isset($configTimezone)
                ? $this->booleanUtils->convert($configTimezone)
                : true;
            if (!$timezone) {
                $date = new \DateTime($date);
            }
            $date = $date->format('Y-m-d H:i:s');
        }
        return $date;
    }

    /**
     * Get Past date
     *
     * @return Date
     */
    public function getMinDate()
    {
        return $this->timezone->date()->format('m/d/Y');
    }
  
    /**
     * Flush Cache
     */
    public function cacheFlush()
    {
        $types = ['full_page'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
    }
    
    /**
     * Media Directory path
     *
     * @return string
     */
    public function mediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function campaignSellerStatus($id)
    {
        $sellerId = $this->marketplaceHelper->getCustomerId();
        $campaign = $this->campaignJoin->create()->getCollection()
                    ->addFieldToFilter('campaign_id', $id)
                    ->addFieldToFilter('seller_id', $sellerId);
        return $campaign->getSize();
    }

    public function campaignStatus($campaignId)
    {
        $item = $this->campaign->create()->load($campaignId)->getData();
        $date = $this->getCurrentDateTime();
        $currentDateTime = $this->getDefaultZoneDateTime($date);
        $currentTimeStamp = strtotime($currentDateTime);
        $startTimeStamp = strtotime($item['start_date']);
        $endTimeStamp = strtotime($item['end_date']);
        $data = [];
        if ($endTimeStamp > $currentTimeStamp && $startTimeStamp < $currentTimeStamp) {
            $data['msg'] = __('During Promotion');
            $data['code'] = CampaignModel::CAMPAIGN_STATUS_RUNNING;
        } elseif ($startTimeStamp > $currentTimeStamp) {
            $data['msg']  = __('Coming Soon');
            $data['code'] = CampaignModel::CAMPAIGN_STATUS_COMMINGSOON;
        } elseif ($endTimeStamp < $currentTimeStamp) {
            $data['msg']  = __('Finish');
            $data['code'] = CampaignModel::CAMPAIGN_STATUS_EXPIRED;
        }
        return $data;
    }

    /**
     * @param object $product
     * @return array|false []
     */
    public function getProductCampainDetail($product)
    {
        //$currentDateTime = $this->getCurrentDateTime();
        /*
        $date = $this->getCurrentDateTime();
        $currentDateTime = $this->getDefaultZoneDateTime($date);
        $currentTimeStamp = strtotime($currentDateTime);
        $allCampain = $this->campaignCollection->create();
        foreach ($allCampain as $cam) {
            $startTimeStamp = strtotime($cam->getStartDate());
            $endTimeStamp = strtotime($cam->getEndDate());
            $campainProduct = $this->campaignProduct->create()
                                ->addFieldToFilter('campaign_id', $cam->getId())
                                ->addFieldToFilter('status', CampaignProModel::STATUS_JOIN);
            if ($endTimeStamp > $currentTimeStamp && $startTimeStamp < $currentTimeStamp) {
                foreach ($campainProduct as $camProduct) {
                    $product = $this->productRepository->getById($camProduct->getProductId());
                    $product->setSpecialFromDate(date("m/d/Y", strtotime($cam->getStartDate())));
                    $product->setSpecialToDate(date("m/d/Y", strtotime($cam->getEndDate())));
                    $product->setSpecialPrice($camProduct->getPrice());
                    $product->setCampaignId($cam->getId());
                    $product->save();
                    if ($camProduct->getProductId() == $product->getId()) {
                        $product = $this->productRepository->getById($camProduct->getProductId());
                    }
                }
            } elseif ($endTimeStamp < $currentTimeStamp) {
                foreach ($campainProduct as $camProduct) {
                    $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                    $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                    $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                    $product->setCampaignId(0);
                    $product->save();
                }
            }
        }*/
        return true;
    }

    public function campainDetail($id)
    {
        $campainDetail = $this->campaignCollection->create()
                        ->addFieldToFilter('entity_id', $id)
                        ->getFirstItem();
        return $campainDetail;
    }

    /**
     * Process file path.
     *
     * @param string $file
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Get temporary base media URL.
     *
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . $this->_prepareFile($file);
    }

    /**
     * Save Campaign products details.
     *
     * @param array
     */
    public function updateCampaignProductsDates($campaignData)
    {
        if (!empty($campaignData['entity_id'])) {
            $startDate = $campaignData['start_date'];
            $endDate = $campaignData['end_date'];
            $startTimeStamp = strtotime($startDate);
            $endTimeStamp = strtotime($endDate);
            $discount = $campaignData['discount'];
            $campaignId = $campaignData['entity_id'];
            $campaignProduct = $this->campaignProduct->create()
                                ->addFieldToFilter('campaign_id', $campaignId);
            foreach ($campaignProduct as $camProduct) {
                $product = $this->productRepository->getById($camProduct->getProductId());
                // calculate updated discounted product price
                $productPrice = $product->getPrice();
                $promotionPrice = ceil($productPrice -($productPrice * $discount/100));
                // update discounted product price to campaign product table
                $camProduct->setPrice($promotionPrice)->save();
                // update campaign details to catalog product
                $this->updateSpecialPriceDates($product->getSku(), $promotionPrice, $startDate, $endDate);
            }
            // clear cache to show updated data on product page
            $this->marketplaceHelper->clearCache();
        }
    }

    public function updateSpecialPriceDates($sku, $promotionPrice, $startDate, $endDate)
    {
        try {
            $prices[] = $this->specialPriceFactory->create()
                ->setSku($sku)
                ->setStoreId(0)
                ->setPrice($promotionPrice)
                ->setPriceFrom($startDate)
                ->setPriceTo($endDate);
            $prices[] = $this->specialPriceFactory->create()
                ->setSku($sku)
                ->setStoreId(1)
                ->setPrice($promotionPrice)
                ->setPriceFrom($startDate)
                ->setPriceTo($endDate);
 
            $product = $this->specialPrice->update($prices);
        } catch (\Exception $e) {
            //throw $e;
        }
    }
}
