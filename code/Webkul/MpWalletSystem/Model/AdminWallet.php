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

use Webkul\MpWalletSystem\Api\Data\AdminWalletInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Webkul MpWalletSystem Model Class
 */
class AdminWallet extends AbstractModel implements AdminWalletInterface, IdentityInterface
{
    const CACHE_TAG = 'wk_mpwallet_admin_wallet';

    const PAY_TYPE_SHIPPING = 1;
    const PAY_TYPE_ORDER_AMOUNT = 2;
    const PAY_TYPE_ORDER_REFUND = 3;
    
    /**
     * @var string
     */
    protected $_cacheTag = 'wk_mpwallet_admin_wallet';
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_mpwallet_admin_wallet';
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWalletSystem\Model\ResourceModel\AdminWallet::class);
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
     * @return \Webkul\Walletsystem\Api\Data\AdminWalletInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Check if shipping invoice is created for admin
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int $type
     * @return boolean
     */
    public function checkInvoiceCreatedForOrder($order, $type)
    {
        return $this->getCollection()
            ->addFieldtoFilter('order_id', $order->getId())
            ->addFieldtoFilter('type', $type)
            ->getSize();
    }

    /**
     * Update admin records after shipping invoice created
     *
     * @param \Magento\Sales\Model\Order $order
     * @param Float $amount
     * @param Int $invoiceId
     * @param Int $type
     * @return boolean
     */
    public function updateInvoiceForAdmin($order, $amount, $type, $invoiced = 1)
    {
        $data = [
            'order_id' => $order->getId(),
            'amount' => $amount,
            'type' => $type,
            'currency_code' => $order->getOrderCurrencyCode(),
            'invoiced' => $invoiced
        ];
        $this->setData($data)
            ->save();
        return true;
    }
}
