<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Model;

use Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class OrderItem extends AbstractModel implements OrderItemInterface, IdentityInterface
{
    /**#@+
     * Shipping Status values
     */
    const STATUS_CANCELLED = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_SHIPPED = 2;
    const STATUS_RECEIVED = 3;

    const CACHE_TAG = 'wk_mp_purchase_order_item';
    const TABLE_NAME = 'wk_mp_purchase_order_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_mp_purchase_order_item';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_mp_purchase_order_item';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpPurchaseManagement\Model\ResourceModel\OrderItem::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getPurchaseOrderId()
    {
        return $this->getData(self::PURCHASE_ORDER_ID);
    }

    public function setPurchaseOrderId($purchaseOrderId)
    {
        return $this->setData(self::PURCHASE_ORDER_ID, $purchaseOrderId);
    }

    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    public function getReceivedQuantity()
    {
        return $this->getData(self::RECEIVED_QUANTITY);
    }

    public function setReceivedQuantity($receivedQuantity)
    {
        return $this->setData(self::RECEIVED_QUANTITY, $receivedQuantity);
    }

    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    public function getShipStatus()
    {
        return $this->getData(self::SHIP_STATUS);
    }

    public function setShipStatus($shipStatus)
    {
        return $this->setData(self::SHIP_STATUS, $shipStatus);
    }

    public function getScheduleDate()
    {
        return $this->getData(self::SCHEDULE_DATE);
    }

    public function setScheduleDate($scheduleDate)
    {
        return $this->setData(self::SCHEDULE_DATE, $scheduleDate);
    }

    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
