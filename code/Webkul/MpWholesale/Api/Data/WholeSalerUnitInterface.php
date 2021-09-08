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

interface WholeSalerUnitInterface
{
    const ENTITY_ID                 = 'entity_id';
    const USER_ID                   = 'user_id';
    const UNIT_NAME                 = 'unit_name';
    const SORT_ORDER                = 'sort_order';
    const STATUS                    = 'status';

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
     * Get Unit Name
     *
     * @return string|null
     */
    public function getUnitName();

    /**
     * Get Sort Order
     *
     * @return int|null
     */
    public function getSortOrder();

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
     * Set User ID
     *
     * @return int|null
     */
    public function setUserId($userId);

    /**
     * Set Unit Name
     *
     * @return string|null
     */
    public function setUnitName($unitName);

    /**
     * Set Sort Order
     *
     * @return int|null
     */
    public function setSortOrder($sortOrder);

    /**
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);
}
