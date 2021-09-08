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

use Webkul\MpWalletSystem\Api\Data\WalletPayeeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Webkul MpWalletSystem Model Class
 */
class WalletPayee extends AbstractModel implements WalletPayeeInterface, IdentityInterface
{
    const CACHE_TAG = 'mpwalletsystem_walletpayee';

    const PAYEE_STATUS_ENABLE = 1;
    const PAYEE_STATUS_DISABLE = 0;
    
    /**
     * @var string
     */
    protected $_cacheTag = 'mpwalletsystem_walletpayee';
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpwalletsystem_walletpayee';
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWalletSystem\Model\ResourceModel\WalletPayee::class);
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
     * @return Webkul\MpWalletSystem\Api\Data\WalletPayeeInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
