<?php

/**
 * Webkul_MpAuction View On Product Block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Account;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action as Act;
use Magento\Framework\App\Action\Context as Contex;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\MpAuction\Model\ProductFactory as MpAuctionProductFactory;

class Check extends Act
{
    /**
     * @var \Magento\Catalog\Block\Product\Context\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProFactory;

    /**
     * @var \Webkul\MpAuction\Model\AmountFactory
     */
    private $aucAmountFactory;

    /**
     * @var \Webkul\MpAuction\Model\AutoAuctionFactory
     */
    private $autoAuctionFactory;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;
    private $mpAuctionProductFactory;

    /**
     * @param Magento\Catalog\Block\Product\Context   $context
     * @param Magento\Catalog\Model\Product           $product
     * @param CustomerSession                         $customerSession
     * @param Magento\Framework\Pricing\Helper\Data   $priceHelper
     * @param Webkul\Auction\Model\ProductFactory     $auctionProFactory
     * @param Webkul\Auction\Model\AmountFactory      $aucAmountFactory
     * @param Webkul\Auction\Model\AutoAuctionFactory $autoAuctionFactory
     * @param Webkul\Auction\Helper\Data              $helperData
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product $product,
        CustomerSession $customerSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Webkul\MpAuction\Model\ProductFactory $auctionProFactory,
        \Webkul\MpAuction\Model\AmountFactory $aucAmountFactory,
        \Webkul\MpAuction\Model\AutoAuctionFactory $autoAuctionFactory,
        \Webkul\MpAuction\Helper\Data $helperData,
        \Magento\Catalog\Block\Product\Context $cont,
        TimezoneInterface $localeDate,
        MpAuctionProductFactory $mpAuctionProductFactory,
        \Magento\Framework\Json\Helper\Data $jsonData
        //TimezoneInterface $localeDate
        // array $data = []
    ) {
        $this->coreRegistry = $cont->getRegistry();
        $this->product = $product;
        $this->customerSession = $customerSession;
        $this->priceHelper = $priceHelper;
        $this->auctionProFactory = $auctionProFactory;
        $this->aucAmountFactory = $aucAmountFactory;
        $this->autoAuctionFactory = $autoAuctionFactory;
        $this->helperData = $helperData;
        $this->_localeDate = $localeDate;
        $this->localeDate = $localeDate;
        $this->_jsonData = $jsonData;
        $this->mpAuctionProductFactory = $mpAuctionProductFactory;
        parent::__construct(
            $context
            //$data
        );
    }

    /**
     * @var $productList Product list of current page
     * @return array of current category product in auction and buy it now
     */
    public function execute()
    {
        $auctionConfig = $this->helperData->getAuctionConfiguration();
        $auctionData = [];
        if ($auctionConfig['enable']) {
            $curPro = $this->coreRegistry->registry('current_product');
            if ($curPro) {
                $currentProId = $curPro->getEntityId();
            } else {
                $auctionId = $this->getRequest()->getParam('id');
                $currentProId = $this->getAuctionProId($auctionId);
                $curPro = $this->product->load($currentProId);
            }
          
            $auctionOpt = $curPro->getAuctionType();
            $auctionOpt = explode(',', $auctionOpt);
            $productId = $this->getRequest()->getParam('id');
            /**
             * 2 : use for auction
             * 1 : use for Buy it now
             */
                $aucDataObj = $this->auctionProFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq'=>$productId])
                                        ->addFieldToFilter('expired', ['eq'=>0])->setPageSize(1)->getFirstItem();
                    $auctionData = $aucDataObj->getData();
            if (isset($auctionData['entity_id'])) {
                $today = $this->_localeDate->date()->format('m/d/y H:i:s');
                $auctionData['start_auction_time']= $this->
                converToTz($auctionData['start_auction_time']);
                $auctionData['stop_auction_time']= $this->
                converToTz($auctionData['stop_auction_time']);
                $stopDate = date_create($auctionData['stop_auction_time']);
                $startDate = date_create($auctionData['start_auction_time']);
                $auctionData['stop_auction_time'] = date_format($stopDate, 'Y-m-d H:i:s');
                $auctionData['start_auction_time'] = date_format($startDate, 'Y-m-d H:i:s');
                $auctionData['current_time_stamp'] = strtotime($today);
                $auctionData['start_auction_time_stamp'] = strtotime($auctionData['start_auction_time']);
                $auctionData['stop_auction_time_stamp'] = strtotime($auctionData['stop_auction_time']);
                $current=$auctionData['current_time_stamp'];
                $stoptime = $auctionData['stop_auction_time_stamp'];
                $difftime= $auctionData['stop_auction_time_stamp'] - $auctionData['current_time_stamp'];
                $response=$this->getResponse()->setHeader('Content-type', 'application/javascript');
                $aucDataObj = $this->auctionProFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq'=>$productId])
                                        ->addFieldToFilter('expired', ['eq'=>0])->setPageSize(1)->getFirstItem();
                $auctionData = $aucDataObj->getData();
                $auctionId = $auctionData['entity_id'];
                $winnerBidDetail = $this->helperData->getWinnerBidDetail($auctionId);
                if ($winnerBidDetail) {
                    $auctionId = $auctionData['entity_id'];
                    $aucDataObj = $this->auctionProFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq'=>$productId])
                                        ->addFieldToFilter('expired', ['eq'=>0])->setPageSize(1)->getFirstItem();
                    $auctionData = $aucDataObj->getData();
                    $productId = $this->getRequest()->getParam('id');
                    $customerId = $winnerBidDetail->getCustomerId();
                    $auctionPro = $this->mpAuctionProductFactory->create()->getCollection()
                                    ->addFieldToFilter('customer_id', $customerId)
                                    ->addFieldToFilter('product_id', $productId)
                                    ->addFieldToFilter('auction_status', 1)->setPageSize(1)->getFirstItem();
                                    $aucAmtData = $this->aucAmountFactory->create()->getCollection()
                                    ->addFieldToFilter('auction_id', $auctionId)
                                    ->addFieldToFilter('winning_status', 1);
                                    $aucDataObj = $this->auctionProFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq'=>$productId])
                                        ->addFieldToFilter('expired', ['eq'=>0])->setPageSize(1)->getFirstItem();
                    $auctionData = $aucDataObj->getData();
                                    $aucAmtData = $this->getFirstItemFromRecord($aucAmtData);
                    $timetobuy=$auctionData['days'];
                }
            }
            if (isset($timetobuy)) {
                $stoptime += $timetobuy*24*60*60;
                $difftime += $timetobuy*24*60*60;
            }
            if (isset($stoptime)) {
                $this->getResponse()->setBody($this->_jsonData
                ->jsonEncode(
                    [
                    'stopauctiontime' =>  $stoptime,
                    'difftime'=>$difftime,
                    'current'=>$current
                    ]
                ));
                return $response;
            }
        }
    }

    /**
     * @return string url of auction form
     */

    public function getAuctionFormAction()
    {
        $curPro = $this->coreRegistry->registry('current_product');
        if ($curPro) {
            $currentProId = $curPro->getEntityId();
        } else {
            $auctionId = $this->getRequest()->getParam('id');
            $currentProId = $this->getAuctionProId($auctionId);
        }
        return $this->customerSession->isLoggedIn() ?
                        $this->_urlBuilder->getUrl("mpauction/account/loginbeforebid"):
                        $this->_urlBuilder->getUrl(
                            'mpauction/account/loginbeforebid/',
                            ['id'=>$currentProId]
                        );
    }

    /**
     * getAuctionDetailAftetEnd
     * @param array $auctionData
     * @param array
     */
    public function getAuctionDetailAftetEnd($auctionData)
    {
        $currentProId = $auctionData['product_id'];
        $auctionId = $auctionData['entity_id'];

        $customerId = 0;
        $shop = '';
        $price = 0;
        $winnerBidDetail = $this->helperData->getWinnerBidDetail($auctionId);
        if ($winnerBidDetail) {
            $customerId = $winnerBidDetail->getCustomerId();
            $shop = $winnerBidDetail->getShop();
            $price = $winnerBidDetail->getBidType() == 'normal' ? $winnerBidDetail->getAuctionAmount():
                                                                $winnerBidDetail->getWinningPrice();
        }

        $waittingList = $this->aucAmountFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq' => $currentProId])
                                        ->addFieldToFilter('auction_id', ['eq' => $auctionId])
                                        ->addFieldToFilter('winning_status', ['neq'=>1]);
        $autoWaittingList = $this->autoAuctionFactory->create()->getCollection()
                                            ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                                            ->addFieldToFilter('flag', ['neq' => 1]);
        $custList=[];

        //get watting winner list from auction amount
        foreach ($waittingList as $waitAuc) {
            if ($waitAuc->getCustomerId()!= $customerId && !in_array($waitAuc->getCustomerId(), $custList)) {
                array_push($custList, $waitAuc->getCustomerId());
            }
        }
        $autoOutBidUser = [];
        //get watting winner list from auto auction
        foreach ($autoWaittingList as $autoWaitAuc) {
            if ($autoWaitAuc->getCustomerId()!= $customerId && !in_array($autoWaitAuc->getCustomerId(), $custList)) {
                array_push($custList, $autoWaitAuc->getCustomerId());
                array_push($autoOutBidUser, $autoWaitAuc->getCustomerId());
            }
        }

        $currentUserWinner = false;
        $currentUserWaitingList = false;
        if ($this->customerSession->isLoggedIn()) {
            $currentCustomerId = $this->helperData->getCurrentCustomerId();
            if ($currentCustomerId == $customerId) {
                $day = strtotime($auctionData['stop_auction_time']. ' + '.$auctionData['days'].' days');
                $difference = $day - $auctionData['current_time_stamp'];
                $currentUserWinner = ['shop' => $shop, 'price' => $price, 'time_for_buy' => $difference];
                if ($difference < 0) {
                    $this->auctionProFactory->create()
                    ->load($auctionId)
                    ->setAuctionStatus(5)
                    ->save();
                }
            }
            
            if (in_array($currentCustomerId, $custList)) {
                $outbidmsg =  __('Bidding has been done for this product.');
                if (in_array($currentCustomerId, $autoOutBidUser)
                 && $this->helperData->getAuctionConfiguration()['enable_auto_outbid_msg']) {
                    $outbidmsg =  $this->helperData->getAuctionConfiguration()['show_auto_outbid_msg'];
                }
                $currentUserWaitingList = [
                    'auc_list_url' => $this->_urlBuilder->getUrl('auction/index/history', ['id'=>$auctionId]),
                    'auc_url_lable' => count($waittingList).__(' Bids'),
                    'msg_lable' => $outbidmsg,
                ];
            }
        }
        return ['watting_user'=> $currentUserWaitingList,'winner' => $currentUserWinner ];
    }

    /**
     * For get Bidding history link
     * @param array $auctionDetail
     * @return string url
     */
    public function getHistoryUrl($auctionData)
    {
        return $this->_urlBuilder->getUrl('mpauction/index/history', ['id'=>$auctionData['entity_id']]);
    }

    /**
     * For get Bidding record count
     * @param int $auctionId
     * @return string
     */
    public function getNumberOfBid($auctionId)
    {
        $records = $this->aucAmountFactory->create()->getCollection()
                                            ->addFieldToFilter('auction_id', ['eq' => $auctionId]);
        return count($records).__(' Bids');
    }

    /**
     * get currency in format
     * @param $amount float
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->priceHelper->currency($amount, true, false);
    }

    /**
     * get auction product id
     * @param $auctionId int
     * @return int
     */
    public function getAuctionProId($auctionId)
    {
        return  $this->auctionProFactory->create()->load($auctionId)->getProductId();
    }

    /**
     * getProAuctionType
     * @return string type of auction
     */
    public function getProAuctionType()
    {
        $curPro = $this->coreRegistry->registry('current_product');
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
        return $this->_localeDate->getConfigTimezone();
    }
    /**
     * getFirstItemFromRecord
     * @param Collection Object
     * @return false | data
     */
    public function getFirstItemFromRecord($collection)
    {
        $row = false;
        foreach ($collection as $row) {
            $row =  $row;
        }
        return $row;
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
