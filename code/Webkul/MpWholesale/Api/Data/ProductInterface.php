<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Api\Data;

interface ProductInterface
{
    const ENTITY_ID     = 'entity_id';
    const USER_ID       = 'user_id';
    const PRODUCT_ID    = 'product_id';
    const PRICE_RULE    = 'price_rule';
    const MIN_ORDER_QTY = 'min_order_qty';
    const MAX_ORDER_QTY = 'max_order_qty';
    const PROD_CAPACITY = 'prod_capacity';
    const DURATION_TYPE = 'duration_type';
    const STATUS        = 'status';
    const APPROVE_STATUS = 'approve_status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get User ID
     *
     * @return int|null
     */
    public function getUserId();

    /**
     * Get Product Id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Get Price Rule
     *
     * @return int|null
     */
    public function getPriceRule();

    /**
     * Get Minimum Order Qty
     *
     * @return int|null
     */
    public function getMinOrderQty();

    /**
     * Get Maximum Order Qty
     *
     * @return int|null
     */
    public function getMaxOrderQty();

    /**
     * Get Production Capacity
     *
     * @return int|null
     */
    public function getProdCapacity();

    /**
     * Get Duration Type
     *
     * @return string|null
     */
    public function getDurationType();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Approve Status
     *
     * @return int|null
     */
    public function getApproveStatus();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setEntityId($id);

    /**
     * Set User ID
     *
     * @return int|null
     */
    public function setUserId($userId);

    /**
     * Set Product Id
     *
     * @return int|null
     */
    public function setProductId($productId);

    /**
     * Set Price Rule
     *
     * @return int|null
     */
    public function setPriceRule($priceRule);

    /**
     * Set MinOrderQty
     *
     * @return int|null
     */
    public function setMinOrderQty($minOrderQty);

    /**
     * Set MaxOrderQty
     *
     * @return int|null
     */
    public function setMaxOrderQty($maxOrderQty);

    /**
     * Set Production Capacity
     *
     * @return int|null
     */
    public function setProdCapacity($prodCapacity);

    /**
     * Set Duration Type
     *
     * @return string|null
     */
    public function setDurationType($durationType);

    /**
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);

    /**
     * Set Approve Status
     *
     * @return int|null
     */
    public function setApproveStatus($approveStatus);
}
