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

interface OrderInterface
{
    const ENTITY_ID                 = 'entity_id';
    const STATUS                    = 'status';
    const WHOLESALER_ID             = 'wholesaler_id';
    const SOURCE                    = 'source';
    const INCREMENT_ID              = 'increment_id';
    const GRAND_TOTAL               = 'grand_total';
    const ORDER_CURRENCY_CODE       = 'order_currency_code';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Wholesaler ID
     *
     * @return int|null
     */
    public function getWholesalerId();

    /**
     * Get Source
     *
     * @return string|null
     */
    public function getSource();

    /**
     * Get Increment Id
     *
     * @return string|null
     */
    public function getIncrementId();

    /**
     * Get Grand Total
     *
     * @return float|null
     */
    public function getGrandTotal();

    /**
     * Get Order Currency Code
     *
     * @return string|null
     */
    public function getOrderCurrencyCode();

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
     * @return int|null
     */
    public function setEntityId($id);

    /**
     * Set Status
     * @param string|null
     */
    public function setStatus($status);

    /**
     * Set Wholesaler ID
     *
     * @return int|null
     */
    public function setWholesalerId($wholesalerId);

    /**
     * Set Source
     *
     * @return string|null
     */
    public function setSource($source);

    /**
     * Set Increment Id
     *
     * @return string|null
     */
    public function setIncrementId($incrementId);

    /**
     * Set Grand Total
     *
     * @return float|null
     */
    public function setGrandTotal($grandTotal);

    /**
     * Set Order Currency Code
     *
     * @return string|null
     */
    public function setOrderCurrencyCode($orderCurrencyCode);

    /**
     * Set Created At
     *
     * @return string|null
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Updated At
     *
     * @return string|null
     */
    public function setUpdatedAt($updatedAt);
}
