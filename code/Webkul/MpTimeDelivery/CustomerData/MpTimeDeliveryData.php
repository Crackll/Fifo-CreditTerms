<?php declare(strict_types=1);
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Store\Model\ScopeInterface;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as MpSellerFactory;

/**
 * Provides data as per private content
 */
class MpTimeDeliveryData extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    const XPATH_ALLOWED_DAY     = 'timeslot/configurations/allowed_days';
    const XPATH_PROCESS_TIME    = 'timeslot/configurations/process_time';
    const XPATH_MAX_DAYS        = 'timeslot/configurations/maximum_days';
    const ENABLE                = 'timeslot/configurations/active';
    const XPATH_MESSAGE         = 'timeslot/configurations/message';
    const ENABLED               = 'timeslot/configurations/active';
    const TIME_ZONE             = 'general/locale/timezone';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Webkul\MpTimeDelivery\Model\TimeSlotOrderFactory
     */
    protected $timeSlotOrderFactory;

    /**
     * @var CollectionFactory
     */
    protected $timeSlotCollection;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var MpSellerFactory
     */
    protected $mpSellerFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\MpTimeDelivery\Model\TimeSlotOrderFactory $timeSlotOrderFactory
     * @param CollectionFactory $timeSlotCollection
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param MpSellerFactory $mpSellerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MpTimeDelivery\Model\TimeSlotOrderFactory $timeSlotOrderFactory,
        CollectionFactory $timeSlotCollection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        MpSellerFactory $mpSellerFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerFactory = $customerFactory;
        $this->_date = $date;
        $this->timeSlotOrderFactory = $timeSlotOrderFactory;
        $this->timeSlotCollection = $timeSlotCollection;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->mpSellerFactory = $mpSellerFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $store = $this->getStoreId();
        $allowedDays = $this->scopeConfig->getValue(self::XPATH_ALLOWED_DAY, ScopeInterface::SCOPE_STORE, $store);
        $processTime = $this->scopeConfig->getValue(self::XPATH_PROCESS_TIME, ScopeInterface::SCOPE_STORE, $store);
        $maxDays = $this->scopeConfig->getValue(self::XPATH_MAX_DAYS, ScopeInterface::SCOPE_STORE, $store);
        $message = $this->scopeConfig->getValue(self::XPATH_MESSAGE, ScopeInterface::SCOPE_STORE, $store);
        $isEnabled = (bool)$this->scopeConfig->getValue(self::ENABLED, ScopeInterface::SCOPE_STORE, $store);
        $timezone = $this->scopeConfig->getValue(self::TIME_ZONE, ScopeInterface::SCOPE_STORE, $store);
        $sellerIds = $this->getSellerIds();

        $date = strtotime("+".$processTime." day", strtotime(date('Y-m-d')));

        $config = [
            'seller' => [],
            'allowed_days' => explode(',', $allowedDays),
            'process_time' => $processTime,
            'start_date'   => date("Y-m-d", $date),
            'max_days'     => $maxDays,
            'isEnabled'    => $isEnabled,
            'timezone' => $timezone
        ];

        if (!$isEnabled) {
            return $config;
        }
        $allowedDays = explode(',', $allowedDays);

        $customerCollection = $this->customerFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("entity_id", ["in" => $sellerIds]);

        foreach ($customerCollection as $customer) {
            $sellerId = $customer->getId();
            $minimum_time =$this->customerRepositoryInterface->getById($sellerId)
                    ->getCustomAttribute('minimum_time_required');
            if ($minimum_time) {
                $minimum_time = $minimum_time->getValue();
                $customerName = $customer->getName();
                if (is_numeric($minimum_time)) {
                        $processTime = $minimum_time;
                }
                $config['seller'][$sellerId] = $this->setSellerSlotData(
                    $sellerId,
                    $allowedDays,
                    $processTime,
                    $maxDays,
                    $customerName,
                    $message
                );
            }
            if (empty($config['seller'][$sellerId])) {
                $processTime = $this->scopeConfig
                    ->getValue(self::XPATH_PROCESS_TIME, ScopeInterface::SCOPE_STORE, $store);
                $config['seller'][$sellerId] = $this->setSellerSlotData(
                    $sellerId,
                    $allowedDays,
                    $processTime,
                    $maxDays,
                    $customer->getName(),
                    $message
                );
            }
        }

        if (in_array(0, $sellerIds)) {
            $sellerId = 0;
            $customerName = __('Admin');
            $processTime = $this->scopeConfig->getValue(self::XPATH_PROCESS_TIME, ScopeInterface::SCOPE_STORE, $store);
            $config['seller'][$sellerId] = $this->setSellerSlotData(
                $sellerId,
                $allowedDays,
                $processTime,
                $maxDays,
                $customerName,
                $message
            );
        }

        return $config;
    }

    /**
     * setSellerSlotData
     *
     * @param [int] $sellerId
     * @param [array] $allowedDays
     * @param [int] $processTime
     * @param [int] $maxDays
     * @param [string] $customerName
     * @param [string] $message
     * @return array
     */
    public function setSellerSlotData(
        $sellerId,
        $allowedDays,
        $processTime,
        $maxDays,
        $customerName,
        $message
    ) {
        $sellerTimeSlotData = $this->getTimeSlotData($sellerId, $allowedDays, $processTime, $maxDays);
        $sellerTimeSlotData['name'] = $customerName;
        $sellerTimeSlotData['message'] = $message;
        return $sellerTimeSlotData;
    }

    /**
     * Retrieve current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * Retrieve current CurrentUrl
     *
     * @return int
     */
    public function getCurrentUrl()
    {
        return $this->storeManager->getStore()->getCurrentUrl(false);
    }

    /**
     * Retrieve seller's ids for quote's items
     *
     * @return array
     */
    private function getSellerIds()
    {
        $sellerIds = [];
        $sellerIds[] = 0;
        $sellerCollection = $this->mpSellerFactory->create()->addFieldTOSelect('seller_id')->getData();

        foreach ($sellerCollection as $data) {
            $sellerIds[] = $data['seller_id'];
        }
        return $sellerIds;
    }

    /**
     * check whether slot is available or not
     *
     * @param object $slot
     * @param int $sellerId
     * @param int $date
     *
     * @return bool
     */
    private function checkAvailabilty($slot, $sellerId, $date)
    {
        $date = $this->_date->gmtDate(date('Y-m-d', $date));
        $collection = $this->timeSlotOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
            ->addFieldToFilter('slot_id', ['eq' => $slot->getEntityId()])
            ->addFieldToFilter('selected_date', ['eq' => $date]);
        if ($collection->getSize() >= $slot->getOrderCount()) {
            return false;
        }
        return true;
    }

    /**
     * return timeSlotCollection for Seller Id
     *
     * @param int $sellerId
     *
     * @return object
     */
    public function getTimeSlotCollection($sellerId)
    {
        $collection = $this->timeSlotCollection->create()
                ->addFieldToFilter('seller_id', ['eq' => $sellerId]);

        if (!$collection->getSize()) {
            $collection = $this->timeSlotCollection->create()
                ->addFieldToFilter('seller_id', ['eq' => 0]);
        }

        return $collection;
    }

    /**
     * return timeSlotCollection for Seller Id
     *
     * @param int $sellerId
     * @param array $allowedDays
     * @param int $processTime
     * @param int $maxDays
     *
     * @return array
     */
    public function getTimeSlotData($sellerId, $allowedDays, $processTime, $maxDays)
    {
        $startDate = 0;
        $dateWiseSlots = $timeSlotData = [];
        $collection = $this->getTimeSlotCollection($sellerId);

        if ($collection->getSize()) {
            foreach ($collection as $slot) {
                if (!in_array($slot->getDeliveryDay(), $allowedDays)) {
                    continue;
                }
                $startTime = $this->_date->gmtDate('h:i A', $slot->getStartTime());
                $endTime = $this->_date->gmtDate('h:i A', $slot->getEndTime());
                $startDate = strtotime("+".$processTime." day", strtotime(date('Y-m-d')));
                
                for ($i=0; $i < $maxDays; $i++) {
                    $d = strtotime("+".$i." day", $startDate);
                    if (ucfirst($slot->getDeliveryDay()) == date('l', $d)) {
                        $isAvailable = $this->checkAvailabilty($slot, $sellerId, $d);
                        $dateWiseSlots[date('Y-m-d', $d)][] = [
                            'slot'=>$startTime.'-'.$endTime,
                            'is_available'=>$isAvailable,
                            'slot_id'   => $slot->getEntityId()
                        ];
                    }
                }
            }
        }

        $startDate = date("Y-m-d", $startDate);
        $timeSlotData['id'] = $sellerId;
        $timeSlotData['slots'] = $dateWiseSlots;
        $timeSlotData['seller_start_date'] = $startDate;
        
        return $timeSlotData;
    }
}
