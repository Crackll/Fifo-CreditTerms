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

class DeductItemDiscountAmountObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @param \Magento\Framework\Event\Manager           $eventManager
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Webkul\Marketplace\Model\ProductFactory   $productFactory
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $session,
        \Webkul\Marketplace\Model\ProductFactory $productFactory
    ) {
        $this->objectManager = $objectManager;
        $this->session = $session;
        $this->productFactory=$productFactory;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $sellerData = $this->getSellerData($order);
        $rewardInfo = $this->session->getRewardInfo();
        $totalSellerAmount = 0;
        $data = [];
        $qty = 1;
        if ($rewardInfo) {
            foreach ($rewardInfo as $info) {
                $perDisPro = 0;
                $totalSellerAmount = $sellerData[$info['seller_id']]['total'];
                $perDisPro = 100 - (((100 * $info['amount']) / $totalSellerAmount));
                foreach ($order->getAllItems() as $item) {
                    if ($item->getQtyOrdered()) {
                        $qty = $item->getQtyOrdered();
                    } else {
                        $qty = $item->getQty();
                    }
                    $options = $item->getProductOptions();
                    $infoBuyRequest = $item->getProductOptionByCode('info_buyRequest');

                    $mpassignproductId = 0;
                    if (isset($infoBuyRequest['mpassignproduct_id'])) {
                        $mpassignproductId = $infoBuyRequest['mpassignproduct_id'];
                    }
                    if ($mpassignproductId) {
                        $mpassignModel = $this->objectManager
                        ->create(\Webkul\MpAssignProduct\Model\Items::class)->load($mpassignproductId);
                        $sellerId = $mpassignModel->getSellerId();
                    } elseif (array_key_exists('seller_id', $infoBuyRequest)) {
                        $sellerId= $infoBuyRequest['seller_id'];
                    } else {
                        $sellerId=0;
                    }
                    if ($sellerId == 0) {
                        $collectionProduct = $this->productFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'mageproduct_id',
                            $item->getProductId()
                        );
                        foreach ($collectionProduct as $value) {
                            $sellerId = $value->getSellerId();
                        }
                    }
                    if ($sellerId == $info['seller_id']) {
                        $data[$item->getProductId()] = [
                            'price' => ($item->getPrice() * $perDisPro) / 100,'qty' => $qty
                        ];
                    }
                }
            }
            $this->session->setData('salelistdata', $data);
        }
    }
    /**
     * prepare seller data from order.
     *
     * @param \Magento\Sale\Model\Order $order
     */
    public function getSellerData($order)
    {
        $sellerData = [];
        $qty = 1;
        foreach ($order->getAllItems() as $item) {
            $sellerId = 0;
            $options = $item->getProductOptions();
            if ($item->getQtyOrdered()) {
                $qty = $item->getQtyOrdered();
            } else {
                $qty = $item->getQty();
            }
            $infoBuyRequest = $item->getProductOptionByCode('info_buyRequest');

            $mpassignproductId = 0;
            if (isset($infoBuyRequest['mpassignproduct_id'])) {
                $mpassignproductId = $infoBuyRequest['mpassignproduct_id'];
            }
            if ($mpassignproductId) {
                $mpassignModel = $this->objectManager
                ->create(\Webkul\MpAssignProduct\Model\Items::class)->load($mpassignproductId);
                $sellerId = $mpassignModel->getSellerId();
            } elseif (array_key_exists('seller_id', $infoBuyRequest)) {
                $sellerId= $infoBuyRequest['seller_id'];
            } else {
                $sellerId = 0;
            }

            if ($sellerId == 0) {
                $collectionProduct = $this->productFactory->create()
                ->getCollection()
                ->addFieldToFilter(
                    'mageproduct_id',
                    $item->getProductId()
                );
                foreach ($collectionProduct as $value) {
                    $sellerId = $value->getSellerId();
                }
            }
            if (!isset($sellerData[$sellerId]['details'])) {
                $sellerData[$sellerId]['details'] = [];
                $sellerData[$sellerId]['total'] = 0;
            }
            array_push(
                $sellerData[$sellerId]['details'],
                [
                    'product_id' => $item->getProductId(),
                    'price' => $item->getPrice() * $qty,
                ]
            );
            $sellerData[$sellerId]['total'] += $item->getPrice() * $qty;
        }

        return $sellerData;
    }
}
