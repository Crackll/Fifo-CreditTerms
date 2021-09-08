<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class CartProductRemoveAfterObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductModel;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $option;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var Session
     */
    protected $session;
     /**
      * @var \Magento\Framework\Json\Helper\Data
      */
    protected $jsonHelper;
    /**
     * @param \Magento\Framework\Event\Manager              $eventManager
     * @param \Magento\Framework\ObjectManagerInterface     $objectManager
     * @param \Webkul\Marketplace\Model\ProductFactory      $mpProductModel
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $optionModel
     * @param \Magento\Framework\Json\Helper\Data           $jsonHelpe
     * @param SessionManager                                $session
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Webkul\Marketplace\Model\ProductFactory $mpProductModel,
        \Magento\Quote\Model\Quote\Item\OptionFactory $optionModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $session
    ) {
        $this->session = $session;
        $this->option = $optionModel;
        $this->objectManager = $objectManager;
        $this->jsonHelper = $jsonHelper;
        $this->mpProductModel = $mpProductModel;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $sellerId = 0;
        $mpassignproductId = 0;
        $item = $observer->getQuoteItem();
        $itemOption = $this->option->create()
                    ->getCollection()
                    ->addFieldToFilter('item_id', $item->getId())
                    ->addFieldToFilter('code', 'info_buyRequest');
        $optionValue = '';
        if ($itemOption->getSize()) {
            foreach ($itemOption as $value) {
                $optionValue = $value->getValue();
            }
        }
        if ($optionValue != '') {
            $temp = $this->jsonHelper->jsonDecode($optionValue);
            $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
        }
        if ($mpassignproductId) {
            $mpassignModel = $this->objectManager
                ->create(\Webkul\MpAssignProduct\Model\Items::class)
              ->load($mpassignproductId);
            $sellerId = $mpassignModel->getSellerId();
        } else {
            $proId = $observer->getQuoteItem()->getProduct()->getId();
            $collectionProduct = $this->mpProductModel->create()
                                ->getCollection()
                                ->addFieldToFilter('mageproduct_id', ['eq' => $proId]);
            foreach ($collectionProduct as $selid) {
                $sellerId = $selid->getSellerId();
            }
        }
        $rewardInfo = $this->session->getRewardInfo();
        unset($rewardInfo[$sellerId]);
        $this->session->unsRewardInfo();
        $this->session->setRewardInfo($rewardInfo);
    }
}
