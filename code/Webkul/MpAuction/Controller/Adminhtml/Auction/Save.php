<?php
/**
 * Webkul MpAuction Save Controller
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Adminhtml\Auction;

class Save extends \Magento\Backend\App\Action
{
    /**
     *
     * @var  \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     *
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localTime;

    /**
     *
     * @var Webkul\MpAuction\Model\ProductFactory
     */
    private $aucProduct;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     * @param \Magento\Catalog\Model\Product      $product
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpAuction\Helper\Data $helperData,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localTime,
        \Webkul\MpAuction\Model\ProductFactory $aucProduct
    ) {
        parent::__construct($context);
        $this->localTime = $localTime;
        $this->helperData = $helperData;
        $this->backendUrl = $backendUrl;
        $this->aucProduct = $aucProduct;
    }

    /**
     * admin html execute
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('mpauction/auction/addauction');
            return;
        }
        try {
            $defaultZone = $this->localTime->getDefaultTimezone();
            $configZone = $this->localTime->getConfigTimezone();

            $auctionProduct = $this->aucProduct->create();

            $data['start_auction_time'] = $this->converToTz($data['start_auction_time'], $defaultZone, $configZone);
            $data['stop_auction_time'] = $this->converToTz($data['stop_auction_time'], $defaultZone, $configZone);
            $todayDateTime = date('m/d/Y 00:00:00');
            if ($todayDateTime > $data['start_auction_time'] || $todayDateTime > $data['stop_auction_time']) {
                $this->messageManager->addError(__("Auction Can't set on the past."));
                $this->_redirect(
                    $this->backendUrl->getUrl(
                        'mpauction/auction/addauction'
                    )
                );
                 return;
            }
          
            $data['min_amount'] = $data['starting_price'];
            if (!isset($data['reserve_price']) || !is_numeric($data['reserve_price'])) {
                $data['reserve_price'] = null;
            }
            if ($data['start_auction_time'] > $data['stop_auction_time']) {
                $this->messageManager->addError(__('Stop auction time must be greater than start auction time.'));
                $this->_redirect(
                    $this->backendUrl->getUrl(
                        'mpauction/auction/addauction',
                        ['id'=>$data['entity_id'],'auction_id'=>$data['entity_id']]
                    )
                );
                return;
            }
            
            $auctionProduct->setData($data);
            //clean by tags
            $this->helperData->cleanByTags($data['product_id']);
            
            if (isset($data['entity_id'])) {
                $auctionProduct->setEntityId($data['entity_id']);
                $getDataById =  $this->aucProduct->create()->load($data['entity_id']);
                if ($getDataById->getStopAuctionTime() < $data['stop_auction_time']) {
                    $auctionProduct->setAuctionStatus(1);
                }
            } else {
                $mpExpAucProducts = $this->aucProduct->create()->getCollection()
                                                        ->addFieldToFilter('product_id', $data['product_id'])
                                                        ->addFieldToFilter('expired', 0);
                foreach ($mpExpAucProducts as $expProduct) {
                    $expProduct->setExpired(1);
                    $this->saveObj($expProduct);
                }
                $auctionProduct->setAuctionStatus(1);
                $auctionProduct->setStatus(0);
            }
            if ($data['min_qty']>$data['max_qty']) {
                $this->messageManager->addError(__('Enter max quantity equal or greater than min quantity.'));
            } else {
                $this->saveObj($auctionProduct);
                $this->messageManager->addSuccess(__('Auction product has been successfully saved.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('mpauction/auction/index');
    }

    /**
     * convert Datetime from one zone to another
     * @param string $dateTime which we want to convert
     * @param string $toTz timezone in which we want to convert
     * @param string $fromTz timezone from which we want to convert
     */
    private function converToTz($dateTime = "", $toTz = '', $fromTz = '')
    {
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('m/d/Y H:i:s');
        return $dateTime;
    }

    /**
     * Check Category Map permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAuction::add_auction');
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
}
