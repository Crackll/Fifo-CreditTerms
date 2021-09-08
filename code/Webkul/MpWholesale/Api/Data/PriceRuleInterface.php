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

interface PriceRuleInterface
{
    const ENTITY_ID                 = 'entity_id';
    const USER_ID                   = 'user_id';
    const RULE_NAME                 = 'rule_name';
    const STATUS                    = 'status';
    const CREATED_DATE              = 'created_date';

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
     * Get Rule Name
     *
     * @return string|null
     */
    public function getRuleName();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Created Date
     *
     * @return string|null
     */
    public function getCreatedDate();

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
     * Set Rule Name
     *
     * @return string|null
     */
    public function setRuleName($ruleName);

    /**
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);

    /**
     * Set Created Date
     *
     * @return string|null
     */
    public function setCreatedDate($createdDate);
}
