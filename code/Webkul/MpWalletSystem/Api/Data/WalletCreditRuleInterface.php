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

namespace Webkul\MpWalletSystem\Api\Data;

/**
 * Webkul MpWalletSystem Interface
 */
interface WalletCreditRuleInterface
{
    const ENTITY_ID = 'entity_id';
    const AMOUNT = 'amount';
    const PRODUCT_IDS = 'product_ids';
    const BASED_ON = 'based_on';
    const MINIMUM_AMOUNT = 'minimum_amount';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const CREATED_AT = 'created_at';
    const STATUS = 'status';

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();
    
    /**
     * Set Entity ID
     *
     * @param  int $id
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setEntityId($id);
    
    /**
     * Get Amount
     *
     * @return float|null
     */
    public function getAmount();
    
    /**
     * Set Amount
     *
     * @param  float $amount
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setAmount($amount);
    
    /**
     * Get Product IDs
     *
     * @return text|null
     */
    public function getProductIds();
    
    /**
     * Set Product ids
     *
     * @param  text $ids
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setProductIds($ids);
    
    /**
     * Get BasedOn
     *
     * @return int|null
     */
    public function getBasedOn();
    
    /**
     * Set Based on
     *
     * @param  int $basedOn
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setBasedOn($basedOn);
    
    /**
     * Get Minimum Amount
     *
     * @return float|null
     */
    public function getMinimumAmount();
    
    /**
     * Set Minimum Amount
     *
     * @param  float $minimumAmount
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setMinimumAmount($minimumAmount);
    
    /**
     * Get Start Date
     *
     * @return date|null
     */
    public function getStartDate();
    
    /**
     * Set Start Date
     *
     * @param  date $startDate
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setStartDate($startDate);
    
    /**
     * Get End Date
     *
     * @return date|null
     */
    public function getEndDate();
    
    /**
     * Set End date
     *
     * @param  date $endDate
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setEndDate($endDate);
    
    /**
     * Get Created at
     *
     * @return date|null
     */
    public function getCreatedAt();
    
    /**
     * Set Craeted at
     *
     * @param  date $createdAt
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setCreatedAt($createdAt);
    
    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();
    
    /**
     * Set Status
     *
     * @param  int $status
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function setStatus($status);
}
