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
namespace Webkul\SampleProduct\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\SampleProduct\Api\Data\SampleProductInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * SampleProduct Product Model.
 *
 * @method \Webkul\SampleProduct\Model\ResourceModel\SampleProduct _getResource()
 * @method \Webkul\SampleProduct\Model\ResourceModel\SampleProduct getResource()
 */
class SampleProduct extends AbstractModel implements SampleProductInterface, IdentityInterface
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
     * SampleProduct Product cache tag.
     */
    const CACHE_TAG = 'sample_product';

    /**
     * @var string
     */
    protected $_cacheTag = 'sample_product';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'sample_product';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\SampleProduct\Model\ResourceModel\SampleProduct::class
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
     * Load No-Route Product.
     *
     * @return \Webkul\SampleProduct\Model\SampleProduct
     */
    public function noRouteProduct()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare product's statuses.
     * Available event sample_product_get_available_statuses to customize statuses.
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
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Product ID.
     *
     * @return int
     */
    public function getProductId()
    {
        return parent::getData(self::PRODUCT_ID);
    }

    /**
     * Set Product ID.
     *
     * @param int $productId
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get Sample Product ID.
     *
     * @return int
     */
    public function getSampleProductId()
    {
        return parent::getData(self::SAMPLE_PRODUCT_ID);
    }

    /**
     * Set Sample Product ID.
     *
     * @param int $sampleProductId
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setSampleProductId($sampleProductId)
    {
        return $this->setData(self::SAMPLE_PRODUCT_ID, $sampleProductId);
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
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
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
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
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
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(self::UPDATED_AT, $timestamp);
    }
}
