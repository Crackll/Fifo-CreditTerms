<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SampleProductOrder\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\SampleProduct\Api\Data\SampleProductOrderInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * SampleProduct Order Model.
 *
 * @method \Webkul\SampleProduct\Model\ResourceModel\SampleProductOrder _getResource()
 * @method \Webkul\SampleProduct\Model\ResourceModel\SampleProductOrder getResource()
 */
class SampleProductOrder extends AbstractModel implements SampleProductOrderInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Product's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * SampleProduct Order cache tag.
     */
    const CACHE_TAG = 'sample_product_order';

    /**
     * @var string
     */
    protected $_cacheTag = 'sample_product_order';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'sample_product_order';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\SampleProduct\Model\ResourceModel\SampleProductOrder::class
        );
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteProduct();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Sample Product Order.
     *
     * @return \Webkul\SampleProduct\Model\SampleProductOrder
     */
    public function noRouteProduct()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Available sample_product_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Yes'),
            self::STATUS_DISABLED => __('No')
        ];
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Sample ID.
     *
     * @return int
     */
    public function getSampleId()
    {
        return parent::getData(self::SAMPLE_PRODUCT_ID);
    }

    /**
     * Set Sample ID.
     *
     * @param int $sampleId
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setSampleId($sampleId)
    {
        return $this->setData(self::SAMPLE_ID, $sampleId);
    }

    /**
     * Get Order ID.
     *
     * @return int
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * Set Order ID.
     *
     * @param int $orderId
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get Status.
     *
     * @return int
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set Status.
     *
     * @param int $status
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get CREATED_AT.
     *
     * @return timestamp
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set CREATED_AT.
     *
     * @param timestamp $timestamp
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setCreatedAt($timestamp)
    {
        return $this->setData(self::CREATED_AT, $timestamp);
    }

    /**
     * Get UPDATED_AT.
     *
     * @return timestamp
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set UPDATED_AT.
     *
     * @param timestamp $timestamp
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(self::UPDATED_AT, $timestamp);
    }
}
