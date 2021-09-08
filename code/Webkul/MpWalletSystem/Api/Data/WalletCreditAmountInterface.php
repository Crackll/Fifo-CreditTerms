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
interface WalletCreditAmountInterface
{
    const ENTITY_ID = 'entity_id';
    const AMOUNT = 'amount';
    const ORDER_ID = 'order_id';
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
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface
     */
    public function setEntityId($id);
    
    /**
     * Get Amount
     *
     * @return int|null
     */
    public function getAmount();
    
    /**
     * Set Amount
     *
     * @param  int $amount
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface
     */
    public function setAmount($amount);
    
    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId();
    
    /**
     * Set Order ID
     *
     * @param  int $ids
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface
     */
    public function setOrderId($ids);
    
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
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface
     */
    public function setStatus($status);
}
