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

use Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Webkul MpWalletSystem Model Class
 */
class Walletcreditamount extends AbstractModel implements WalletCreditAmountInterface, IdentityInterface
{
    const WALLET_CREDIT_AMOUNT_STATUS_ENABLE = 1;
    const WALLET_CREDIT_AMOUNT_STATUS_DISABLE = 0;

    const CACHE_TAG = 'mpwalletsystem_walletcreditamount';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'mpwalletsystem_walletcreditamount';
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpwalletsystem_walletcreditamount';
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWalletSystem\Model\ResourceModel\Walletcreditamount::class);
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
     * Get Entity ID
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
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Amount
     *
     * @param int $amount
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface
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
     * Set Order ID
     *
     * @param int $orderId
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditAmountInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
}
