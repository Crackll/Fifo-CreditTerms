<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Model;

use Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * Webkul MpWalletSystem Model Class
 */
class Walletcreditrules extends AbstractModel implements WalletCreditRuleInterface, IdentityInterface
{
    const WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_PRODUCT = 1;
    const WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_RULE = 2;
    const WALLET_CREDIT_RULE_BASED_ON_PRODUCT = 1;
    const WALLET_CREDIT_RULE_BASED_ON_CART = 0;

    const WALLET_CREDIT_CONFIG_AMOUNT_TYPE_FIXED = 0;
    const WALLET_CREDIT_CONFIG_AMOUNT_TYPE_PERCENT = 1;

    const WALLET_CREDIT_CONFIG_PRIORITY_PRODUCT_BASED = 0;
    const WALLET_CREDIT_CONFIG_PRIORITY_CART_BASED = 1;

    const WALLET_CREDIT_RULE_STATUS_ENABLE = 1;
    const WALLET_CREDIT_RULE_STATUS_DISABLE = 0;
   
    const CACHE_TAG = 'mpwalletsystem_walletcreditrules';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'mpwalletsystem_walletcreditrules';
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpwalletsystem_walletcreditrules';
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWalletSystem\Model\ResourceModel\Walletcreditrules::class);
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

    /**
     * Set Entity ID
     *
     * @param int $id
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Amount
     *
     * @param int $amount
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Get Amount
     *
     * @return int $id
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * Set Product Ids
     *
     * @param int $productIds
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setProductIds($productIds)
    {
        return $this->setData(self::PRODUCT_IDS, $productIds);
    }

    /**
     * Get Product Ids
     *
     * @return string
     */
    public function getProductIds()
    {
        return $this->getData(self::PRODUCT_IDS);
    }
    
    /**
     * Set Based On
     *
     * @param int $basedOn
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setBasedOn($basedOn)
    {
        return $this->setData(self::BASED_ON, $basedOn);
    }
    
    /**
     * Get Based On
     *
     * @return int
     */
    public function getBasedOn()
    {
        return $this->getData(self::BASED_ON);
    }

    /**
     * Set Minimum Amount
     *
     * @param int $minimimAmount
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setMinimumAmount($minimimAmount)
    {
        return $this->setData(self::MINIMUM_AMOUNT, $minimimAmount);
    }

    /**
     * Get Minimum Amount
     *
     * @return int
     */
    public function getMinimumAmount()
    {
        return $this->getData(self::MINIMUM_AMOUNT);
    }

    /**
     * Set Start Date
     *
     * @param date $startDate
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Get Start Date
     *
     * @return date
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * Set End Date
     *
     * @param date $endDate
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * Get End Date
     *
     * @return date
     */
    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
    }

    /**
     * Set Created At
     *
     * @param timestamp $createdAt
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Created At
     *
     * @return timestamp
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Status
     *
     * @param int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
}
