<?php
/**
 * Webkul_MpAuction bid save controller for login user
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Url as UrlModel;
use Magento\Framework\Stdlib\DateTime;

class LoginBeforeBid extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $dirCurrencyFactory;

    /**
     * @var UrlModel
     */
    private $urlModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timeZone;

    /**
     * @var \Webkul\MpAuction\Model\AmountFactory
     */
    private $auctionAmount;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @var \Webkul\MpAuction\Model\AutoAuctionFactory
     */
    private $autoAuction;

    /**
     * @param Context                                     $context
     * @param PageFactory                                 $resultPageFactory
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory    $dirCurrencyFactory
     * @param RequestInterface                            $requesMpt
     * @param UrlModel                                    $urlModel
     * @param \Webkul\MpAuction\Model\AmountFactory       $auctionAmount
     * @param \Webkul\MpAuction\Model\ProductFactory      $auctionProductFactory
     * @param \Webkul\MpAuction\Helper\Data               $helperData
     * @param \Webkul\MpAuction\Model\AutoAuctionFactory  $autoAuction
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory,
        TimezoneInterface $timeZone,
        UrlModel $urlModel,
        \Webkul\MpAuction\Model\AmountFactory $auctionAmount,
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpAuction\Helper\Data $helperData,
        \Webkul\MpAuction\Helper\Email $helperEmail,
        \Webkul\MpAuction\Model\AutoAuctionFactory $autoAuction,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->dirCurrencyFactory = $dirCurrencyFactory;
        $this->timeZone = $timeZone;
        $this->urlModel = $urlModel;
        $this->auctionAmount = $auctionAmount;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->helperData = $helperData;
        $this->helperEmail = $helperEmail;
        $this->autoAuction = $autoAuction;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->urlModel->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            //set Data in session if bidder not login
            $this->customerSession->setAuctionBidData($request->getParams());
        }
        return parent::dispatch($request);
    }

    /**
     * Default customer account page
     * @var $cuntCunyCode current Currency Code
     * @var $baseCunyCode base Currency Code
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        try {
            $data = $this->customerSession->getMessages(true);
            //get data from customerSession relared to Auction
            $data = $this->customerSession->getAuctionBidData();
            $auctionData = $data = $data ? $data: $this->getRequest()->getParams();
            $productId = $auctionData['product_id'];
            //clean by tags
            $this->helperData->cleanByTags($productId);
            $resultRedirect = $this->resultRedirectFactory->create();
            if (!isset($auctionData['pro_url']) || $auctionData['pro_url']=='') {
                $auctionData['pro_url'] = $this->_url->getBaseUrl();
            }
            $biddingData = $this->auctionProductFactory->create()->load($auctionData['entity_id']);
            $customerid = $this->helperData->getCurrentCustomerId();

            if ($biddingData->getCustomerId() == $customerid) {
                $this->messageManager->addError(__('You can not be able to place bid on your own product.'));
                return $resultRedirect->setUrl($auctionData['pro_url']);
            }
            
            $this->customerSession->unsAuctionBidData();
            //unset data of customerSession relared to Auction
            $this->customerSession->unsAuctionBidData();
            $this->customerSession->unsAuctionData();

            $today = $this->timeZone->date()->format('m/d/y H:i:s');
            $difference = $auctionData['stop_auction_time_stamp'] - strtotime($today);

            if ($difference > 0) {
                //auction configuration detail
                $auctionConfig = $this->helperData->getAuctionConfiguration();

                $data['auction_id'] = $auctionData['entity_id'];
                $data['product_id'] = $auctionData['product_id'];
                $data['pro_name'] = $auctionData['pro_name'];
                $data['bidding_amount'] = $data['bidding_amount']/$this->getCurrentCurrencyRate();
                $biddingModel = $this->auctionProductFactory->create()->load($data['auction_id']);
                if ($auctionConfig['auto_enable'] && $auctionData['auto_auction_opt']
                    && array_key_exists("auto_bid_allowed", $data)) {
                    $this->saveAutobiddingAmount($data);
                } else {
                    $this->saveBiddingAmount($data);
                }
                $this->sendOutbidMails($data);
                if ($biddingModel->getMinAmount()==$biddingModel->getStartingPrice()) {
                    $this->productFactory->create()->load($data['product_id'])->setAuctionType('2')->save();
                    ;
                }
            } else {
                $this->messageManager->addError(__('Auction time expired...'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong...'));
        }
        
        return $resultRedirect->setUrl($auctionData['pro_url']);
    }

    /**
     * saveBiddingAmount saves amount of normal bid placed by customer
     * @param array $data stores bid data
     * @var int $val bid amount
     * @var int $userId current logged in customer's id
     * @var object $biddingModel holds data for particular bid
     * @var $minPrice int stores minimum price of bidding
     * @var $incopt stores increament option is allowed on bidding or not
     * @var $incPrice holds increment price for product
     * @var $bidCusrRecord object id customer already placed a bid
     * @var $bidModel use to strore new bidding
     */
    
    private function saveBiddingAmount($data)
    {
        $sendUserId = "";
        $customerArray = [];
        $val = $data['bidding_amount'] * $this->getCurrentCurrencyRate();
        $auctionConfig = $this->helperData->getAuctionConfiguration();
        $userId = $this->helperData->getCurrentCustomerId();
        $data['current_user_id'] = $userId;
        $biddingModel = $this->auctionProductFactory->create()->load($data['auction_id']);
        $minPrice = $biddingModel->getMinAmount();
        $currentAuctionPriceData = $this->auctionAmount->create()->getCollection()
            ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
            ->addFieldToFilter('auction_id', ['eq'=> $data['auction_id']])
            ->setOrder('auction_amount', 'DESC')
            ->getFirstItem();
        if ($currentAuctionPriceData->getAuctionAmount()) {
            $minPrice = $currentAuctionPriceData->getAuctionAmount();
        }
        if ($biddingModel->getIncrementOpt() && $auctionConfig['increment_auc_enable']) {
            $incVal = $this->helperData->getIncrementPriceAsRange($biddingModel);
            $minPrice = $incVal ? $minPrice + $incVal : $minPrice;
        }
        $minPrice = ($minPrice + 0.009999999999) * $this->getCurrentCurrencyRate();
        if ($minPrice >= $val) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can not bid less than or equal to next minimum bid amount.')
            );
        } else {
            $bidCusrRecord = $this->auctionAmount->create()->getCollection()
                                    ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
                                    ->addFieldToFilter('customer_id', ['eq' => $userId])
                                    ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                    ->addFieldToFilter('status', ['eq' => '1'])->setPageSize(1)->getFirstItem();
            if ($bidCusrRecord->getEntityId()) {
                $bidCusrRecord->setId($bidCusrRecord->getEntityId());
                $bidCusrRecord->setAuctionAmount($data['bidding_amount']);
                $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                $bidCusrRecord->setCreatedAt($todayDate);
                $bidCusrRecord->setIsAutoBid(0);
                $bidCusrRecord->save();
            } else {
                $bidModel = $this->auctionAmount->create();
                $bidModel->setAuctionAmount($data['bidding_amount']);
                $bidModel->setCustomerId($userId);
                $bidModel->setProductId($data['product_id']);
                $bidModel->setAuctionId($data['auction_id']);
                $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                $bidModel->setCreatedAt($todayDate);
                $bidModel->setStatus(1);
                $bidModel->setIsAutoBid(0);
                $bidModel->save();
            }
            
            if ($auctionConfig['enable_submit_bid_email']) {
                $this->helperEmail->sendSubmitMailToBidder($userId, $data['product_id'], $data['bidding_amount']);
            }
            $this->updateAutoBidForNormalbidding($data);
            $this->notifyToAdminAndSeller(
                $auctionConfig['enable_admin_email'],
                $auctionConfig['enable_seller_email'],
                $data,
                $userId
            );
            $biddingModel->setMinAmount($val/$this->getCurrentCurrencyRate());
            $biddingModel->save();
            $this->messageManager->addSuccess(__('Your bid amount successfully saved.'));
        }
    }

    //If Auto bid is higher then current normal bid
    public function updateAutoBidForNormalbidding($data)
    {
        $biddingModel = $this->auctionProductFactory->create()->load($data['auction_id']);
        $maxAutoBid = $this->autoAuction->create()->getCollection()
                                ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                ->addFieldToFilter('status', ['eq' => '1'])
                                ->addFieldToFilter('amount', ['gt' => $data['bidding_amount']])
                                ->addFieldToFilter('customer_id', ['neq' => $data['current_user_id']])
                                ->setOrder('amount', 'desc')
                                ->getFirstItem();
        if ($maxAutoBid->getEntityId()) {
            $incVal = $this->helperData->getIncrementPriceAsRange($biddingModel);
            $data['auto_bid_amount'] = $incVal ? $data['bidding_amount'] + $incVal : $data['bidding_amount'] + 0.01;
            $userId = $maxAutoBid->getCustomerId();
            $bidCusrRecord = $this->auctionAmount->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
                                        ->addFieldToFilter('customer_id', ['eq' => $userId])
                                        ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                        ->addFieldToFilter('status', ['eq' => '1'])->setPageSize(1)->getFirstItem();
            if ($bidCusrRecord->getId()) {
                if ($bidCusrRecord->getAuctionAmount()<$data['auto_bid_amount']) {
                    $bidCusrRecord->setId($bidCusrRecord->getEntityId());
                    $bidCusrRecord->setAuctionAmount($data['auto_bid_amount']);
                    $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                    $bidCusrRecord->setCreatedAt($todayDate);
                    $bidCusrRecord->setIsAutoBid(1);
                    $bidCusrRecord->save();
                }
            } else {
                $bidModel = $this->auctionAmount->create();
                $bidModel->setAuctionAmount($data['auto_bid_amount']);
                $bidModel->setCustomerId($userId);
                $bidModel->setProductId($data['product_id']);
                $bidModel->setAuctionId($data['auction_id']);
                $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                $bidModel->setCreatedAt($todayDate);
                $bidModel->setStatus(1);
                $bidModel->setIsAutoBid(1);
                $bidModel->save();
            }
            $biddingModel->setMinAmount($data['auto_bid_amount'])->save();
            $this->helperEmail->sendAutobidMailToBidder(
                $userId,
                $data['product_id'],
                $maxAutoBid->getAmount(),
                $data['auto_bid_amount']
            );
        }
    }

    /**
     * saveAutobiddingAmount calls to store auto bid placed by customer
     * @param array $data holda data related to bidding
     * @var $userId int holds current customer id
     * @var $biddingModel object stores bidding details
     * @var $auctionConfig['auto_use_increment'] int stores whether increment option is enable in admin panel or not
     * @var $auctionConfig['auto_auc_limit'] stores whether customer can place auto bid multiple times or not
     * @var $minPrice int stores minimum price of bidding
     * @var $incopt stores increament option is allowed on bidding or not
     * @var $incprice holds increment price for product
     * @var $pid int product id on which bid is placed
     * @var $val int bidding amount placed by customer
     * @var $autoBidRecord object checks whether there is already bid already exist for current customer or not
     * @var $autoBidModel autobid model to store auto bid
     * @var $listToSendMail use to get maximum auto bid amount
     */

    private function saveAutobiddingAmount($data)
    {
        $max=0;
        $data['nb_amount'] = $data['bidding_amount'];
        $val = $data['bidding_amount'] * $this->getCurrentCurrencyRate();
        $userId = $this->helperData->getCurrentCustomerId();
        $data['current_user_id'] = $userId;
        $auctionConfig = $this->helperData->getAuctionConfiguration();
        $biddingModel = $this->auctionProductFactory->create()->load($data['auction_id']);
        $changeprice = $minPrice = $biddingModel->getMinAmount();

        if ($biddingModel->getIncrementOpt() && $auctionConfig['auto_use_increment']) {
            $incVal = $this->helperData->getIncrementPriceAsRange($biddingModel);
            $changeprice = $minPrice = $incVal ? $minPrice + $incVal : $minPrice;
            $tempNbAmounts = [];
            $tempNbAmounts[] = $minPrice;
            $maxAutoBid = $this->autoAuction->create()->getCollection()
                                ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                ->addFieldToFilter('status', ['eq' => '1'])
                                ->addFieldToFilter('customer_id', ['neq' => $userId])
                                ->setOrder('amount', 'desc')
                                ->getFirstItem();
            if ($maxAutoBid->getEntityId()) {
                $tempNbAmounts[] = $incVal ? $maxAutoBid->getAmount() + $incVal : $maxAutoBid->getAmount();
            }
            if (max($tempNbAmounts)<=$data['bidding_amount']) {
                $data['nb_amount'] = max($tempNbAmounts);
            }
        }

        $minPrice = ($minPrice + 0.009999999999) * $this->getCurrentCurrencyRate();
    
        if ($minPrice >= $val) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can not auto bid less than or equal to next minimum bid amount.')
            );
        } else {
            $autoBidRecord = $this->autoAuction->create()->getCollection()
                                    ->addFieldToFilter('customer_id', ['eq' => $userId])
                                    ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                    ->addFieldToFilter('status', ['eq' => '1'])->setPageSize(1)->getLastItem();
            if ($autoBidRecord->getEntityId()) {
                if (!$auctionConfig['auto_auc_limit']) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You are not allowed to auto bid again.')
                    );
                } else {
                    $autoBidRecord->setId($autoBidRecord->getEntityId());
                    $autoBidRecord->setAmount($data['bidding_amount']);
                    $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                    $autoBidRecord->setCreatedAt($todayDate);
                    $autoBidRecord->save();
                }
            } else {
                $autoBidModel = $this->autoAuction->create();
                $autoBidModel->setAmount($data['bidding_amount']);
                $autoBidModel->setCustomerId($userId);
                $autoBidModel->setProductId($data['product_id']);
                $autoBidModel->setAuctionId($data['auction_id']);
                $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                $autoBidModel->setCreatedAt($todayDate);
                $autoBidModel->setStatus(1);
                $autoBidModel->save();
            }
            if ($auctionConfig['enable_submit_bid_email']) {
                $this->helperEmail->sendAutoSubmitMailToBidder($userId, $data['product_id'], $data['bidding_amount']);
            }
            $this->createNormalBidForAutobidding($data);
            $this->notifyToAdminAndSeller(
                $auctionConfig['enable_admin_email'],
                $auctionConfig['enable_seller_email'],
                $data,
                $userId,
                'auto'
            );
            if ($auctionConfig['enable_auto_outbid_msg'] && $max > $val) {
                $this->messageManager->addError(__($auctionConfig['show_auto_outbid_msg']));
            } else {
                $biddingModel->setMinAmount($changeprice/$this->getCurrentCurrencyRate());
                $biddingModel->save();
                $this->messageManager->addSuccess(__('Your auto bid amount successfully saved'));
            }
            return ;
        }
    }

    /**
     * Save the Normal bidding for the auto bidding
     */
    public function createNormalBidForAutobidding($data)
    {
        $biddingModel = $this->auctionProductFactory->create()->load($data['auction_id']);
        $bidCusrRecord = $this->auctionAmount->create()->getCollection()
                                ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
                                ->addFieldToFilter('customer_id', ['eq' => $data['current_user_id']])
                                ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                ->addFieldToFilter('status', ['eq' => '1'])->setPageSize(1)->getFirstItem();
        if ($bidCusrRecord->getId()) {
            if ($bidCusrRecord->getAuctionAmount()<$data['nb_amount']) {
                $bidCusrRecord->setId($bidCusrRecord->getEntityId());
                $bidCusrRecord->setAuctionAmount($data['nb_amount']);
                $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                $bidCusrRecord->setCreatedAt($todayDate);
                $bidCusrRecord->setIsAutoBid(1);
                $bidCusrRecord->save();
            }
        } else {
            $bidModel = $this->auctionAmount->create();
            $bidModel->setAuctionAmount($data['nb_amount']);
            $bidModel->setCustomerId($data['current_user_id']);
            $bidModel->setProductId($data['product_id']);
            $bidModel->setAuctionId($data['auction_id']);
            $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
            $bidModel->setCreatedAt($todayDate);
            $bidModel->setStatus(1);
            $bidModel->setIsAutoBid(1);
            $bidModel->save();
        }
        $biddingModel->setMinAmount($data['nb_amount'])->save();
        $this->helperEmail->sendAutobidMailToBidder(
            $data['current_user_id'],
            $data['product_id'],
            $data['bidding_amount'],
            $data['nb_amount']
        );

        //check if the user out bid
        $maxAutoBid = $this->autoAuction->create()->getCollection()
                                ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                ->addFieldToFilter('status', ['eq' => '1'])
                                ->addFieldToFilter('customer_id', ['neq' => $data['current_user_id']])
                                ->setOrder('amount', 'desc')
                                ->getFirstItem();
        if ($maxAutoBid->getAmount() > $data['bidding_amount']) {
            $userId = $maxAutoBid->getCustomerId();

            $bidCusrRecord = $this->auctionAmount->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
                                        ->addFieldToFilter('customer_id', ['eq' => $userId])
                                        ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                        ->addFieldToFilter('status', ['eq' => '1'])->setPageSize(1)->getFirstItem();
            $incVal = $this->helperData->getIncrementPriceAsRange($biddingModel);
            $data['nb_amount_second'] = $incVal ? $data['bidding_amount'] + $incVal : $data['bidding_amount'] + 0.01;
            if ($bidCusrRecord->getId()) {
                if ($bidCusrRecord->getAuctionAmount()<$data['nb_amount_second']) {
                    $bidCusrRecord->setId($bidCusrRecord->getEntityId());
                    $bidCusrRecord->setAuctionAmount($data['nb_amount_second']);
                    $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                    $bidCusrRecord->setCreatedAt($todayDate);
                    $bidCusrRecord->setIsAutoBid(1);
                    $bidCusrRecord->save();
                }
            } else {
                $bidModel = $this->auctionAmount->create();
                $bidModel->setAuctionAmount($data['nb_amount_second']);
                $bidModel->setCustomerId($userId);
                $bidModel->setProductId($data['product_id']);
                $bidModel->setAuctionId($data['auction_id']);
                $todayDate = $this->timeZone->date()->format('Y-m-d H:i:s');
                $bidModel->setCreatedAt($todayDate);
                $bidModel->setStatus(1);
                $bidModel->setIsAutoBid(1);
                $bidModel->save();
            }
            $biddingModel->setMinAmount($data['nb_amount_second'])->save();
            $this->helperEmail->sendAutobidMailToBidder(
                $userId,
                $data['product_id'],
                $maxAutoBid->getAmount(),
                $data['nb_amount_second']
            );
        }
    }

    public function getCurrentCurrencyRate()
    {
        $store = $this->storeManager->getStore();
        $currencyModel = $this->dirCurrencyFactory->create();
        $baseCunyCode = $store->getBaseCurrencyCode();
        $cuntCunyCode = $store->getCurrentCurrencyCode();

        $allowedCurrencies = $currencyModel->getConfigAllowCurrencies();
        $rates = $currencyModel->getCurrencyRates($baseCunyCode, array_values($allowedCurrencies));

        $rates[$cuntCunyCode] = isset($rates[$cuntCunyCode]) ? $rates[$cuntCunyCode] : 1;
        return $rates[$cuntCunyCode];
    }

    public function sendOutbidMails($data)
    {
        $auctionConfig = $this->helperData->getAuctionConfiguration();
        if ($auctionConfig['enable_outbid_email']) {
            $maxUserId = 0;
            $listNormalBids = $this->auctionAmount->create()->getCollection()
                                    ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
                                    ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                    ->setOrder('auction_amount', 'DESC');
            $i = 0;
            $autoBiddersOut = [];
            foreach ($listNormalBids as $normalBid) {
                if ($i == 0) {
                    $maxUserId = $normalBid->getCustomerId();
                } else {
                    if ($normalBid->getIsAutoBid()) {
                        $this->helperEmail->sendAutoMailUsers($normalBid->getCustomerId(), $data['product_id']);
                    } else {
                        $this->helperEmail->sendMailToMembers($normalBid->getCustomerId(), $data['product_id']);
                    }
                }
                $i++;
            }
        }
    }

    /**
     * @param boolen $adminEnable
     * @param boolen $sellerEnable
     * @param array $data
     * @param int $userId
     * @param string $type
     * @return void
     */
    private function notifyToAdminAndSeller($adminNotifyEnable, $sellerNotifyEnable, $data, $userId, $type = 'normal')
    {
        if ($adminNotifyEnable) {
            if ($type == 'normal') {
                $this->helperEmail->sendMailToAdmin($userId, $data['product_id'], $data['bidding_amount']);
            } else {
                $this->helperEmail->sendAutoMailToAdmin($userId, $data['product_id'], $data['bidding_amount']);
            }
        }

        if ($sellerNotifyEnable) {
            if ($type == 'normal') {
                $this->helperEmail->sendMailToSeller($userId, $data['product_id'], $data['bidding_amount']);
            } else {
                $this->helperEmail->sendAutoMailToSeller($userId, $data['product_id'], $data['bidding_amount']);
            }
        }
    }
}
