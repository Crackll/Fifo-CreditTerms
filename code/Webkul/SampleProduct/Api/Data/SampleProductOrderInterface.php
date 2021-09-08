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

interface SampleProductOrderInterface
{
    const ENTITY_ID = 'entity_id';
    const SAMPLE_ID = 'sample_id';
    const ORDER_ID = 'order_id';
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
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setId($id);

    /**
     * Get Sample ID.
     *
     * @return int|null
     */
    public function getSampleId();

    /**
     * Set Sample ID.
     *
     * @param int $sampleId
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setSampleId($sampleId);

    /**
     * Get Order ID.
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set Order ID.
     *
     * @param int $orderId
     *
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setOrderId($orderId);

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
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setStatus($status);

    /**
     * Gets creation timestamp.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Sets creation timestamp.
     *
     * @param string $timestamp
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setCreatedAt($timestamp);

    /**
     * Gets updated_at timestamp.
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Sets updated_at timestamp.
     *
     * @param string $timestamp
     * @return \Webkul\SampleProduct\Api\Data\SampleProductOrderInterface
     */
    public function setUpdatedAt($timestamp);
}
