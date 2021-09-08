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

use Webkul\MpWholesale\Api\Data\QuoteInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Quotes extends AbstractModel implements QuoteInterface, IdentityInterface
{

    const STATUS_UNAPPROVED = 1;
    const STATUS_APPROVED = 2;
    const STATUS_DECLINE = 3;

    const CACHE_TAG = 'wholesaler_quotes';
    /**
     * @var string
     */
    protected $_cacheTag = 'wholesaler_quotes';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wholesaler_quotes';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWholesale\Model\ResourceModel\Quotes::class);
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

    public function setWholesalerId($wholesalerId)
    {
        return $this->setData(self::WHOLESALER_ID, $wholesalerId);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getWholesaleProductId()
    {
        return $this->getData(self::WHOLESALE_PRODUCT_ID);
    }

    public function setWholesaleProductId($wholesaleProductId)
    {
        return $this->setData(self::WHOLESALE_PRODUCT_ID, $wholesaleProductId);
    }

    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    public function getQuoteQty()
    {
        return $this->getData(self::QUOTE_QTY);
    }

    public function setQuoteQty($quoteQty)
    {
        return $this->setData(self::QUOTE_QTY, $quoteQty);
    }

    public function getQuotePrice()
    {
        return $this->getData(self::QUOTE_PRICE);
    }

    public function setQuotePrice($quotePrice)
    {
        return $this->setData(self::QUOTE_PRICE, $quotePrice);
    }

    public function getQuoteMsg()
    {
        return $this->getData(self::QUOTE_MSG);
    }

    public function setQuoteMsg($quoteMsg)
    {
        return $this->setData(self::QUOTE_MSG, $quoteMsg);
    }

    public function getQuoteCurrencyCode()
    {
        return $this->getData(self::QUOTE_CURRENCY_CODE);
    }

    public function setQuoteCurrencyCode($quoteCurrencyCode)
    {
        return $this->setData(self::QUOTE_CURRENCY_CODE, $quoteCurrencyCode);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
