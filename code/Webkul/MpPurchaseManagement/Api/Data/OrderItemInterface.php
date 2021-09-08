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

namespace Webkul\MpPurchaseManagement\Api\Data;

interface OrderItemInterface
{
    const ENTITY_ID                 = 'entity_id';
    const PURCHASE_ORDER_ID         = 'purchase_order_id';
    const QUOTE_ID                  = 'quote_id';
    const SELLER_ID                 = 'seller_id';
    const PRODUCT_ID                = 'product_id';
    const SKU                       = 'sku';
    const QUANTITY                  = 'quantity';
    const RECEIVED_QUANTITY         = 'received_qty';
    const WEIGHT                    = 'weight';
    const SHIP_STATUS               = 'ship_status';
    const SCHEDULE_DATE             = 'schedule_date';
    const PRICE                     = 'price';
    const CURRENCY_CODE             = 'currency_code';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Purchase Order ID
     *
     * @return int|null
     */
    public function getPurchaseOrderId();

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getQuoteId();

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Get Product ID
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Get Quantity
     *
     * @return int|null
     */
    public function getQuantity();

    /**
     * Get Received Quantity
     *
     * @return int|null
     */
    public function getReceivedQuantity();

    /**
     * Get Weight
     *
     * @return float|null
     */
    public function getWeight();

    /**
     * Get Ship Status
     *
     * @return int|null
     */
    public function getShipStatus();

    /**
     * Get Schedule Date
     *
     * @return string|null
     */
    public function getScheduleDate();

    /**
     * Get Price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Get Currency Code
     *
     * @return string|null
     */
    public function getCurrencyCode();

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set ID
     *
     * @param int
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setEntityId($id);

    /**
     * Set Purchase Order ID
     *
     * @param int
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setPurchaseOrderId($purchaseOrderId);

    /**
     * Set Quote ID
     *
     * @param int
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setQuoteId($quoteId);

    /**
     * Set Seller ID
     *
     * @param int
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setSellerId($sellerId);

    /**
     * Set Product ID
     *
     * @param int
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setProductId($productId);

    /**
     * Set Sku
     *
     * @param string
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setSku($sku);

    /**
     * Set Quantity
     *
     * @param int
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setQuantity($quantity);

    /**
     * Set Received Quantity
     *
     * @param int
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setReceivedQuantity($receivedQuantity);

    /**
     * Set Weight
     *
     * @param float
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setWeight($weight);

    /**
     * Set Ship Status
     *
     * @param int
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setShipStatus($shipStatus);

    /**
     * Set Schedule Date
     *
     * @param string
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setScheduleDate($scheduleDate);

    /**
     * Set Price
     *
     * @param float
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setPrice($price);

    /**
     * Set Currency Code
     *
     * @param string
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Set Created At
     *
     * @param string
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Updated At
     *
     * @param string
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function setUpdatedAt($updatedAt);
}
