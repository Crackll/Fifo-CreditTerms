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

use Webkul\MpWholesale\Api\Data\LeadInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Leads extends AbstractModel implements LeadInterface, IdentityInterface
{
    const CACHE_TAG = 'wholesaler_product_leads';
    /**
     * @var string
     */
    protected $_cacheTag = 'wholesaler_product_leads';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wholesaler_product_leads';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWholesale\Model\ResourceModel\Leads::class);
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

    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    public function getWholesalerId()
    {
        return $this->getData(self::WHOLESALER_ID);
    }

    public function setWholesalerId($wholeSalerId)
    {
        return $this->setData(self::WHOLESALER_ID, $wholeSalerId);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    public function getViewCount()
    {
        return $this->getData(self::VIEW_COUNT);
    }

    public function setViewCount($viewCount)
    {
        return $this->setData(self::VIEW_COUNT, $viewCount);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getViewAt()
    {
        return $this->getData(self::VIEW_AT);
    }

    public function setViewAt($viewAt)
    {
        return $this->setData(self::VIEW_AT, $viewAt);
    }

    public function getRecentViewAt()
    {
        return $this->getData(self::RECENT_VIEW_AT);
    }

    public function setRecentViewAt($recentViewAt)
    {
        return $this->setData(self::RECENT_VIEW_AT, $recentViewAt);
    }
}
