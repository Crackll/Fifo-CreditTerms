<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Model;

use Webkul\MpPurchaseManagement\Api\Data\OrderInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Order extends AbstractModel implements OrderInterface, IdentityInterface
{
    /**
     * Order Status values
     */
    const STATUS_CANCELLED = 0;
    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_SHIPPED = 3;
    const STATUS_COMPLETE = 4;

    const CACHE_TAG = 'wk_mp_purchase_order';
    const TABLE_NAME = 'wk_mp_purchase_order';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_mp_purchase_order';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_mp_purchase_order';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpPurchaseManagement\Model\ResourceModel\Order::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getWholesalerId()
    {
        return $this->getData(self::WHOLESALER_ID);
    }

    public function setWholesalerId($wholesalerId)
    {
        return $this->setData(self::WHOLESALER_ID, $wholesalerId);
    }

    public function getSource()
    {
        return $this->getData(self::SOURCE);
    }

    public function setSource($source)
    {
        return $this->setData(self::SOURCE, $source);
    }

    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    public function getGrandTotal()
    {
        return $this->getData(self::GRAND_TOTAL);
    }

    public function setGrandTotal($grandTotal)
    {
        return $this->setData(self::GRAND_TOTAL, $grandTotal);
    }

    public function getOrderCurrencyCode()
    {
        return $this->getData(self::ORDER_CURRENCY_CODE);
    }

    public function setOrderCurrencyCode($orderCurrencyCode)
    {
        return $this->setData(self::ORDER_CURRENCY_CODE, $orderCurrencyCode);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
