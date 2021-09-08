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

interface LeadInterface
{
    const ENTITY_ID                 = 'entity_id';
    const SELLER_ID                 = 'seller_id';
    const WHOLESALER_ID             = 'wholesaler_id';
    const PRODUCT_ID                = 'product_id';
    const PRODUCT_NAME              = 'product_name';
    const VIEW_COUNT                = 'view_count';
    const STATUS                    = 'status';
    const VIEW_AT                   = 'view_at';
    const RECENT_VIEW_AT            = 'recent_view_at';

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
     * Get WholeSaler ID
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
     * Get Product Name
     *
     * @return string|null
     */
    public function getProductName();

    /**
     * Get View Count
     *
     * @return int|null
     */
    public function getViewCount();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get View At
     *
     * @return string|null
     */
    public function getViewAt();

    /**
     * Get  Recent View At
     *
     * @return string|null
     */
    public function getRecentViewAt();

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
     * Set WholeSaler ID
     *
     * @return int|null
     */
    public function setWholesalerId($wholeSalerId);

    /**
     * Set Product Id
     *
     * @return int|null
     */
    public function setProductId($productId);

    /**
     * Set Product Name
     *
     * @return string|null
     */
    public function setProductName($productName);

    /**
     * Set View Count
     *
     * @return int|null
     */
    public function setViewCount($viewCount);

    /**
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);

    /**
     * Set View At
     *
     * @return string|null
     */
    public function setViewAt($viewAt);

    /**
     * Set  Recent View At
     *
     * @return string|null
     */
    public function setRecentViewAt($recentViewAt);
}
