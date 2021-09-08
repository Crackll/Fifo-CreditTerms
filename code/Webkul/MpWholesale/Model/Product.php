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

namespace Webkul\MpWholesale\Model;

use Webkul\MpWholesale\Api\Data\ProductInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Product extends AbstractModel implements ProductInterface, IdentityInterface
{
    const CACHE_TAG = 'mpwholesale_product_details';
    /**
     * @var string
     */
    protected $_cacheTag = 'mpwholesale_product_details';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpwholesale_product_details';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWholesale\Model\ResourceModel\Product::class);
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
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getPriceRule()
    {
        return $this->getData(self::PRICE_RULE);
    }

    public function setPriceRule($priceRule)
    {
        return $this->setData(self::PRICE_RULE, $priceRule);
    }

    public function getMinOrderQty()
    {
        return $this->getData(self::MIN_ORDER_QTY);
    }

    public function setMinOrderQty($minOrderQty)
    {
        return $this->setData(self::MIN_ORDER_QTY, $minOrderQty);
    }

    public function getMaxOrderQty()
    {
        return $this->getData(self::MAX_ORDER_QTY);
    }

    public function setMaxOrderQty($maxOrderQty)
    {
        return $this->setData(self::MAX_ORDER_QTY, $maxOrderQty);
    }

    public function getProdCapacity()
    {
        return $this->getData(self::PROD_CAPACITY);
    }

    public function setProdCapacity($prodCapacity)
    {
        return $this->setData(self::PROD_CAPACITY, $prodCapacity);
    }

    public function getDurationType()
    {
        return $this->getData(self::DURATION_TYPE);
    }

    public function setDurationType($durationType)
    {
        return $this->setData(self::DURATION_TYPE, $durationType);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getApproveStatus()
    {
        return $this->getData(self::APPROVE_STATUS);
    }

    public function setApproveStatus($approveStatus)
    {
        return $this->setData(self::APPROVE_STATUS, $approveStatus);
    }
}
