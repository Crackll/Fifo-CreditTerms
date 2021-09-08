<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Api\Data;

interface RewardproductInterface
{
    const ENTITY_ID   = 'entity_id';
    const PRODUCT_ID  = 'product_id';
    const SELLER_ID   = 'seller_id';
    const POINTS      = 'points';
    const STATUS      = 'status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Product ID
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Get Points
     *
     * @return int|null
     */
    public function getPoints();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setEntityId($id);

    /**
     * Set Product ID
     *
     * @return int|null
     */
    public function setProductId($productId);

    /**
     * Set Seller ID
     *
     * @return int|null
     */
    public function setSellerId($sellerId);

    /**
     * Set Points
     *
     * @return int|null
     */
    public function setPoints($point);

    /**
     * Set status
     *
     * @return int|null
     */
    public function setStatus($status);
}
