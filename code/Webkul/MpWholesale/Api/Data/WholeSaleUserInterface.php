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

interface WholeSaleUserInterface
{
    const ENTITY_ID                 = 'entity_id';
    const USER_ID                   = 'user_id';
    const TITLE                     = 'title';
    const DESCRIPTION               = 'description';
    const ADDRESS                   = 'address';
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
     * Get Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get Address
     *
     * @return string|null
     */
    public function getAddress();

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
     * Set Title
     *
     * @return string|null
     */
    public function setTitle($title);

    /**
     * Set Description
     *
     * @return string|null
     */
    public function setDescription($description);

    /**
     * Set Address
     *
     * @return string|null
     */
    public function setAddress($address);

    /**
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);
}
