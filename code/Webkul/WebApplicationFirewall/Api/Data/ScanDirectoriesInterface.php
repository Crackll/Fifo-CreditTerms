<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */

namespace Webkul\WebApplicationFirewall\Api\Data;

/**
 * ScanDirectoriesDataInterface interface
 */
interface ScanDirectoriesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ENTITY_ID = 'entity_id';
    const PATH = 'path';
    const PERMISSION = 'permission';
    const MODIFIED_AT = 'modified_at';
    const IS_NEW = 'is_new';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesDataInterface
     */
    public function setId($entityId);

    /**
     * Get directory path
     * @return string|null
     */
    public function getPath();

    /**
     * Set directory path
     * @param string $path
     * @return \Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesDataInterface
     */
    public function setPath($path);

    /**
     * Get directory permission data
     * @return string|null
     */
    public function getPermission();

    /**
     * Set directory permission data
     * @param string $password
     * @return \Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesDataInterface
     */
    public function setPermission($permission);

    /**
     * Get directory modified at
     * @return string|null
     */
    public function getModifiedAt();

    /**
     * Set directory modified at
     * @param string $date
     * @return \Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesDataInterface
     */
    public function setModifiedAt($date);

    /**
     * Get is new directory
     * @return bool|null
     */
    public function getIsNew();

    /**
     * Set is new directory
     * @param bool $isNew
     * @return \Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesDataInterface
     */
    public function setIsNew($isNew);
}
