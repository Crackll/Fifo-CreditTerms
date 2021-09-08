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

interface RewardcategoryInterface
{
    const ENTITY_ID   = 'entity_id';
    const CATEGORY_ID = 'category_id';
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
     * Get Category ID
     *
     * @return int|null
     */
    public function getCategoryId();

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
     * Set Category ID
     *
     * @return int|null
     */
    public function setCategoryId($categoryId);

    /**
     * set Seller ID
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
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);
}
