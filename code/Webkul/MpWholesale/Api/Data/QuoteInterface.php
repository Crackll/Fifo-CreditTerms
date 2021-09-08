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

interface QuoteInterface
{
    const ENTITY_ID                 = 'entity_id';
    const SELLER_ID                 = 'seller_id';
    const WHOLESALER_ID             = 'wholesaler_id';
    const PRODUCT_ID                = 'product_id';
    const WHOLESALE_PRODUCT_ID      = 'wholesale_product_id';
    const PRODUCT_NAME              = 'product_name';
    const QUOTE_QTY                 = 'quote_qty';
    const QUOTE_PRICE               = 'quote_price';
    const QUOTE_MSG                 = 'quote_msg';
    const QUOTE_CURRENCY_CODE       = 'quote_currency_code';
    const STATUS                    = 'status';
    const CREATED_AT                = 'created_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Get Wholesaler ID
     *
     * @return int|null
     */
    public function getWholesalerId();

    /**
     * Get Product Id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Get Wholesale Product Id
     *
     * @return int|null
     */
    public function getWholesaleProductId();

    /**
     * Get Product Name
     *
     * @return string|null
     */
    public function getProductName();

    /**
     * Get Quote Qty
     *
     * @return int|null
     */
    public function getQuoteQty();

    /**
     * Get Quote Price
     *
     * @return float|null
     */
    public function getQuotePrice();

    /**
     * Get Quote Message
     *
     * @return string|null
     */
    public function getQuoteMsg();

    /**
     * Get Quote Currency Code
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setEntityId($id);

    /**
     * Set Seller ID
     *
     * @return int|null
     */
    public function setSellerId($sellerId);

    /**
     * Set Wholesaler ID
     *
     * @return int|null
     */
    public function setWholesalerId($wholesalerId);

    /**
     * Set Product Id
     *
     * @return int|null
     */
    public function setProductId($productId);

    /**
     * Set Wholesale Product Id
     *
     * @return int|null
     */
    public function setWholesaleProductId($wholesaleProductId);

    /**
     * Set Product Name
     *
     * @return string|null
     */
    public function setProductName($productName);

    /**
     * Set Quote Qty
     *
     * @return int|null
     */
    public function setQuoteQty($quoteQty);

    /**
     * Set Quote Price
     *
     * @return float|null
     */
    public function setQuotePrice($quotePrice);

    /**
     * Set Quote Message
     *
     * @return string|null
     */
    public function setQuoteMsg($quoteMsg);

    /**
     * Set Quote Currency Code
     *
     * @return string|null
     */
    public function setQuoteCurrencyCode($quoteCurrencyCode);

    /**
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);

    /**
     * Set Created At
     *
     * @return string|null
     */
    public function setCreatedAt($createdAt);
}
