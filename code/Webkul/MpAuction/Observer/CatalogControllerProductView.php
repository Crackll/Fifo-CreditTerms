<?php
/**
 * Webkul_MpAuction Product View Observer.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\MpAuction\Helper\Data as HelperData;
use Webkul\MpAuction\Helper\Email as HelperEmail;
use Webkul\MpAuction\Model\ProductFactory as AuctionProductFactory;
use Webkul\MpAuction\Model\AmountFactory as AuctionAmount;
use Webkul\MpAuction\Model\AutoAuctionFactory as AutoAuction;
use Webkul\MpAuction\Model\WinnerDataFactory as WinnerData;

/**
 * Reports Event observer model.
 */
class CatalogControllerProductView implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $dateTime;

    /**
     * @var Configurable
     */
    private $configurableProTypeModel;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @var \Webkul\MpAuction\Helper\Email
     */
    private $helperEmail;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

    /**
     * @var \Webkul\MpAuction\Model\Amount
     */
    private $auctionAmount;

    /**
     * @var \Webkul\MpAuction\Model\AutoAuction
     */
    private $autoAuction;

    /**
     * @var \Webkul\MpAuction\Model\WinnerData
     */
    private $winnerData;

    /**
     * $resPrice
     * @var float
     */
    private $resPrice;

    /**
     * $bidDay
     * @var int
     */
    private $bidDay;

    /**
     * $bidId bid id
     * @var int
     */
    private $bidId;

    /**
     * $incPrice
     * @var float
     */
    private $incPrice;

    /**
     * $datestop auction stop date
     * @var string
     */
    private $datestop;

    /**
     * $datestart auction start date
     * @var string
     */
    private $datestart;

    /**
     * $winDataTemp winner data
     * @var array
     */
    private $winDataTemp = [];

    /**
     * $auctionConfig auction config
     * @var array
     */
    private $auctionConfig = [];

    /**
     * @param TimezoneInterface              $dateTime
     * @param Configurable          $configProTypeModel
     * @param HelperData            $helperData
     * @param HelperEmail           $helperEmail
     * @param AuctionProductFactory $auctionProductFactory
     * @param AuctionAmount         $auctionAmount
     * @param AutoAuction           $autoAuction
     * @param WinnerData            $winnerData
     */

    public function __construct(
        TimezoneInterface $dateTime,
        Configurable $configProTypeModel,
        HelperData $helperData,
        HelperEmail $helperEmail,
        AuctionProductFactory $auctionProductFactory,
        AuctionAmount $auctionAmount,
        AutoAuction $autoAuction,
        WinnerData $winnerData,
        \Psr\Log\LoggerInterface $logger,
        TimezoneInterface $localeDate
    ) {
        $this->dateTime = $dateTime;
        $this->configurableProTypeModel = $configProTypeModel;
        $this->helperData = $helperData;
        $this->helperEmail = $helperEmail;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->auctionAmount = $auctionAmount;
        $this->autoAuction = $autoAuction;
        $this->winnerData = $winnerData;
        $this->logger = $logger;
        $this->localeDate = $localeDate;
    }

    /**
     * View Catalog Product View observer.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->auctionConfig = $this->helperData->getAuctionConfiguration();
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        if ($product && $this->auctionConfig['enable']) {
            $productId = $product->getId();
            if ($product->getTypeId() == 'configurable') {
                $childPro = $this->configurableProTypeModel->getChildrenIds($productId);
                $childProIds = isset($childPro[0]) ? $childPro[0]:[0];
                $auctionActPro = $this->auctionProductFactory->create()->getCollection()
                                            ->addFieldToFilter('product_id', ['in' => $childProIds])
                                            ->addFieldToFilter('auction_status', 1)
                                            ->addFieldToFilter('status', 1);
                if ($auctionActPro->getSize()) {
                    foreach ($auctionActPro as $value) {
                        $this->biddingOperation($value->getProductId());
                    }
                }
            } else {
                $this->biddingOperation($productId);
            }
        }
        return $this;
    }

    /**
     * biddingOperation
     * @param int $productId on which auction apply
     * @return void
     */
    private function biddingOperation($productId)
    {
        $auctionActPro = $this->getAuctionProduct($productId);
        if ($auctionActPro && ($this->datestop >= $this->datestart)) {
            $this->winDataTemp['auction_id'] = $auctionActPro->getEntityId();
            $today = $this->dateTime->date()->format('m/d/y H:i:s');
            $current = strtotime($today);
            $difference = $this->datestop - $current;

            if ($difference <= 0) {
                $auctionActPro->setIsProcessing(1)->save();
                $this->saveWinnerData($auctionActPro);
                $auctionActPro->setIsProcessing(0)->save();

            } else {
                $winnerDataModel = $this->winnerData->create()->getCollection()
                                                ->addFieldToFilter('product_id', ['eq' => $productId])
                                                ->addFieldToFilter('status', ['eq'=>1])->setPageSize(1);
                $winnerDataModel = $this->getFirstItemFromRecord($winnerDataModel);
                                                
                if ($winnerDataModel && $winnerDataModel->getEntityId()) {
                    $this->bidDay = $winnerDataModel->getDays() ? 1: $winnerDataModel->getDays();
                    $current = strtotime($this->dateTime->date()->format('m/d/y H:i:s'));
                    $day = strtotime($winnerDataModel->getStopBiddingTime().' + '.$this->bidDay.' days');
                    $difference = $day - $current;
                    if ($difference < 0) {
                        $winnerDataModel->setStatus(0);
                        $winnerDataModel->setId($winnerDataModel->getEntityId());
                        $this->saveObj($winnerDataModel);
                    }
                }
            }
        }
    }

    /**
     * getAuctionProduct get auction product
     * @param  int $productId
     * @return Object
     */
    private function getAuctionProduct($productId)
    {
        $auctionProduct =  $this->auctionProductFactory->create()->getCollection()
                                                        ->addFieldToFilter('product_id', ['eq' => $productId])
                                                        ->addFieldToFilter('is_processing', ['eq' => 0])
                                                        ->addFieldToFilter('auction_status', 1)
                                                        ->addFieldToFilter('status', 0)->setPageSize(1);
        $auctionProduct = $this->getFirstItemFromRecord($auctionProduct);

        if ($auctionProduct && $auctionProduct->getEntityId()) {
            $this->resPrice = 0;
            $this->resPriceConfig = $this->auctionConfig['reserve_enable'] ? $this->auctionConfig['reserve_price'] : '';
            
            $this->bidDay = $auctionProduct->getDays();
            $this->bidId = $auctionProduct->getEntityId();
            
            $this->incPrice = $auctionProduct->getIncrementPrice();
            $this->resPrice = $auctionProduct->getReservePrice();
           
            $this->resPrice = $this->resPrice == 0 ? $this->resPriceConfig:$this->resPrice;
            $this->datestop = strtotime($this->converToTz($auctionProduct->getStopAuctionTime()));
            $this->datestart = strtotime($this->converToTz($auctionProduct->getStartAuctionTime()));
            return $auctionProduct;
        }

        return false;
    }

    /**
     * getWinnerData save winner
     * @param  Webkul\MpAuction\Model\ProductFactory $auctionActPro
     * @return void
     */
    private function saveWinnerData($auctionActPro)
    {
        $productId = $auctionActPro->getProductId();
        $bidArray = [0];
        $bidAmount = $this->auctionAmount->create()->getCollection()
                                ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                                ->addFieldToFilter('status', ['eq' => 1])
                                ->setOrder('auction_amount', 'desc')
                                ->getFirstItem();

        if ($bidAmount->getId()) {
            $this->winDataTemp['customer_id'] = $bidAmount->getCustomerId();
            $this->winDataTemp['amount'] = $bidAmount->getAuctionAmount();
            $this->winDataTemp['type'] = $bidAmount->getIsAutoBid()?'auto':'normal';
        }

        if (isset($this->winDataTemp['customer_id']) && $this->resPrice <= $this->winDataTemp['amount']) {
            if ($this->winDataTemp['type'] == 'auto') {
                $this->winnerByAutoBid($productId);
            } elseif ($this->winDataTemp['type']== 'normal') {
                $this->winnerByNormalBid($productId);
            }
            //save winner record in winner data table
            $winnerDataModel = $this->winnerData->create();
            $auctionModel = $this->auctionProductFactory->create()->load($this->bidId)->getData();
            $auctionModel['customer_id'] = $this->winDataTemp['customer_id'];
            $auctionModel['status'] = 1;
            $auctionModel['auction_id'] = $auctionModel['entity_id'];
            $auctionModel['win_amount'] = $this->winDataTemp['amount'];
            unset($auctionModel['entity_id']);
            $winnerDataModel->setcustomerId($auctionModel['customer_id']);
            $winnerDataModel->setData($auctionModel);
            $winnerDataModel->save();
            $auctionActPro->setAuctionStatus(0);
        } elseif (isset($this->winDataTemp['type'])
        && $this->auctionConfig['auto_enable'] && $this->winDataTemp['type'] == 'auto') {
            $autoBiddList = $this->autoAuction->create()->getCollection()
                ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                ->addFieldToFilter('status', 1);
            
            foreach ($autoBiddList as $autoBid) {
                if ($autoBid->getCustomerId() == $this->winDataTemp['customer_id']) {
                    $autoBid->setFlag(1);
                    if ($auctionActPro->getReservePrice()===null ||
                        $autoBid->getAmount()>=$auctionActPro->getReservePrice()
                    ) {
                        $autoBid->setWinningPrice($autoBid->getAmount());
                        if ($this->auctionConfig['enable_winner_notify_email']) {
                            $this->logger->info(json_encode($this->winDataTemp['customer_id']));
                            $this->helperEmail->sendWinnerMail(
                                $this->winDataTemp['customer_id'],
                                $productId,
                                $autoBid->getAmount()
                            );
                        }
                    } else {
                        $autoBid->setWinningPrice($auctionActPro->getReservePrice());
                        /** send notification mail to winner */
                        if ($this->auctionConfig['enable_winner_notify_email']) {
                            $this->logger->info(json_encode($this->winDataTemp['customer_id']));
                            $this->helperEmail->sendWinnerMail(
                                $this->winDataTemp['customer_id'],
                                $productId,
                                $auctionActPro->getReservePrice()
                            );
                        }
                    }

                } elseif ($autoBid->getCustomerId() != $this->winDataTemp['customer_id']) {
                    $autoBid->setFlag(2);
                }
                $autoBid->setStatus(0);
                $autoBid->setId($autoBid->getEntityId());
                $this->saveObj($autoBid);
            }
            //save winner record in winner data table
            $winnerDataModel = $this->winnerData->create();
            $auctionModel = $this->auctionProductFactory->create()->load($this->bidId)->getData();
            $auctionModel['customer_id'] = $this->winDataTemp['customer_id'];
            $auctionModel['status'] = 1;
            $auctionModel['auction_id'] = $auctionModel['entity_id'];
            if ($auctionActPro->getReservePrice()===null || $autoBid->getAmount()>=$auctionActPro->getReservePrice()) {
                $auctionModel['win_amount'] = $autoBid->getAmount();
            } else {
                $auctionModel['win_amount'] = $auctionActPro->getReservePrice();
            }
            unset($auctionModel['entity_id']);
            $winnerDataModel->setData($auctionModel);
            
            $this->saveObj($winnerDataModel);
            $auctionActPro->setAuctionStatus(0);
        } else {
            $auctionActPro->setAuctionStatus(2);
        }
        $auctionActPro->setId($auctionActPro->getEntityId());
        $this->saveObj($auctionActPro);
         //clean by tags
         $this->helperData->cleanByTags($productId);
    }

    /**
     * winnerByAutoBid
     * @param int $productId
     * @return void
     */
    private function winnerByAutoBid($productId)
    {
        $this->logger->info("winbyautobid");
        $autoBiddList = $this->autoAuction->create()->getCollection()
                                    ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                                    ->addFieldToFilter('status', 1);
        $normalBidList = $this->auctionAmount->create()->getCollection()
                        ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                        ->addFieldToFilter('status', ['eq' => 1]);
        
        foreach ($autoBiddList as $autoBid) {
            $autoBid->setFlag(2);
            if ($autoBid->getCustomerId() == $this->winDataTemp['customer_id']) {
                $autoBid->setFlag(1);
                $autoBid->setWinningPrice($this->winDataTemp['amount']);
                // send notification mail to winner
                if ($this->auctionConfig['enable_winner_notify_email']) {
                    $this->helperEmail->sendWinnerMail(
                        $this->winDataTemp['customer_id'],
                        $productId,
                        $this->winDataTemp['amount']
                    );
                }
            }
            $autoBid->setStatus(0);
            $autoBid->setId($autoBid->getEntityId());
            $this->saveObj($autoBid);
        }
        foreach ($normalBidList as $normalBid) {
            $normalBid->setWinningStatus(2);
            $normalBid->setStatus(0);
            $normalBid->setId($normalBid->getEntityId());
            $this->saveObj($normalBid);
        }
    }

    /**
     * winnerByNormalBid
     * @param int $productId
     * @return void
     */
    private function winnerByNormalBid($productId)
    {
        $normalBidList = $this->auctionAmount->create()->getCollection()
                                            ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                                            ->addFieldToFilter('status', ['eq' => 1]);
        $autoBiddList = $this->autoAuction->create()->getCollection()
                ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                ->addFieldToFilter('status', 1);
        foreach ($normalBidList as $normalBid) {
            if ($normalBid->getCustomerId() == $this->winDataTemp['customer_id']) {
                $normalBid->setWinningStatus(1);
                // send notification mail to winner
                if ($this->auctionConfig['enable_winner_notify_email']) {
                    $this->helperEmail->sendWinnerMail(
                        $this->winDataTemp['customer_id'],
                        $productId,
                        $this->winDataTemp['amount']
                    );
                }
            } elseif ($normalBid->getCustomerId() != $this->winDataTemp['customer_id']) {
                $normalBid->setWinningStatus(2);
            }
            $normalBid->setStatus(0);
            $normalBid->setId($normalBid->getEntityId());
            $this->saveObj($normalBid);
        }
        foreach ($autoBiddList as $autoBid) {
            $autoBid->setFlag(2);
            $autoBid->setStatus(0);
            $autoBid->setId($autoBid->getEntityId());
            $this->saveObj($autoBid);
            
        }
    }
    
    /**
     * calculateIfAutoBidEnable
     * @param array $bidArray
     * @param \Webkul\MpAuction\Model\Product $auctionActPro
     * @return void
     */
    private function calculateIfAutoBidEnable($bidArray, $auctionActPro)
    {
        $autoBidArray = [0];
        $autoBidList = $this->autoAuction->create()->getCollection()
                                    ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                                    ->addFieldToFilter('status', ['eq' => 1]);

        if (!empty($bidArray)) {
            $autoBidList->addFieldToFilter('amount', ['gteq'=> max($bidArray)]);
            $temp = $autoBidList->getColumnValues('amount');
            $bidArray = array_merge($temp, $bidArray);
            // $this->winDataTemp['amount'] = max($bidArray);
        } else {
            $this->resPrice = $auctionActPro->getReservePrice();
            $starPrice = $auctionActPro->getStartingPrice();
            // $this->winDataTemp['amount'] = $this->resPrice ? $this->resPrice : $starPrice;
        }

        if ($autoBidList->getSize()) {
            foreach ($autoBidList as $autoBid) {
                $custId = $autoBid->getCustomerId();
                $autoBidArray[$custId] = $autoBid->getAmount();
            }

            $customerIds = array_keys($autoBidArray, max($autoBidArray));
            if (max($autoBidArray) >= $this->resPrice) {
                $this->winDataTemp['customer_id'] = $customerIds[0];
                $this->winDataTemp['type'] = 'auto';
            }
        }
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
     * getFirstItemFromRecord
     * @param Collection Object
     * @return false | data
     */
    private function getFirstItemFromRecord($collection)
    {
        foreach ($collection as $row) {
            return $row;
        }
        return false;
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
}
