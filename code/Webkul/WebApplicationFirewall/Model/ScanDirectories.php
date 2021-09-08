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

namespace Webkul\WebApplicationFirewall\Model;

use Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesInterface;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesInterfaceFactory;

/**
 * WAF ScanDirectories class
 */
class ScanDirectories extends \Magento\Framework\Model\AbstractModel implements ScanDirectoriesInterface
{

    protected $dataObjectHelper;

    protected $_eventPrefix = 'webkul_waf_directories_scan';

    protected $scandirectoriesDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScanDirectoriesInterfaceFactory $scandirectoriesDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Webkul\WebApplicationFirewall\Model\ResourceModel\ScanDirectories $resource
     * @param \Webkul\WebApplicationFirewall\Model\ResourceModel\ScanDirectories\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScanDirectoriesInterfaceFactory $scandirectoriesDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Webkul\WebApplicationFirewall\Model\ResourceModel\ScanDirectories $resource,
        \Webkul\WebApplicationFirewall\Model\ResourceModel\ScanDirectories\Collection $resourceCollection,
        array $data = []
    ) {
        $this->scandirectoriesDataFactory = $scandirectoriesDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->_get(self::PATH);
    }

    /**
     * @inheritDoc
     */
    public function setPath($path)
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * @inheritDoc
     */
    public function getPermission()
    {
        return $this->_get(self::PERMISSION);
    }

    /**
     * @inheritDoc
     */
    public function setPermission($permission)
    {
        return $this->setData(self::PERMISSION, $permission);
    }

    /**
     * @inheritDoc
     */
    public function getModifiedAt()
    {
        return $this->_get(self::MODIFIED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setModifiedAt($modifiedAt)
    {
        return $this->setData(self::MODIFIED_AT, $modifiedAt);
    }

    /**
     * @inheritDoc
     */
    public function getIsNew()
    {
        return $this->_get(self::IS_NEW);
    }

    /**
     * @inheritDoc
     */
    public function setIsNew($isNew)
    {
        return $this->setData(self::IS_NEW, $isNew);
    }
}
