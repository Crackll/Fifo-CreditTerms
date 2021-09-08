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
namespace Webkul\SampleProduct\Api\Data;

interface SampleProductInterface
{
    const ENTITY_ID = 'entity_id';
    const PRODUCT_ID = 'product_id';
    const SAMPLE_PRODUCT_ID = 'sample_product_id';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setId($id);

    /**
     * Get Product ID.
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set Product ID.
     *
     * @param int $productId
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setProductId($productId);

    /**
     * Get Sample Product ID.
     *
     * @return int|null
     */
    public function getSampleProductId();

    /**
     * Set Sample Product ID.
     *
     * @param int $sampleProductId
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setSampleProductId($sampleProductId);

    /**
     * Get Status.
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set Status.
     *
     * @param int $status
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setStatus($status);

    /**
     * Gets creation timestamp.
     *
     * @return timestamp
     */
    public function getCreatedAt();

    /**
     * Sets creation timestamp.
     *
     * @param timestamp $timestamp
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setCreatedAt($timestamp);

    /**
     * Gets updated_at timestamp.
     *
     * @return timestamp
     */
    public function getUpdatedAt();

    /**
     * Sets updated_at timestamp.
     *
     * @param timestamp $timestamp
     * @return \Webkul\SampleProduct\Api\Data\SampleProductInterface
     */
    public function setUpdatedAt($timestamp);
}
