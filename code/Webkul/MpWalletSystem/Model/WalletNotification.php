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

use Webkul\MpWalletSystem\Api\Data\WalletnotificationInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Webkul MpWalletSystem Model Class
 */
class WalletNotification extends AbstractModel implements walletnotificationInterface, IdentityInterface
{

    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * WalletNotification WalletNotification cache tag
     */
    const CACHE_TAG = 'mpwalletsystem_walletnotification';

    /**
     * @var string
     */
    protected $_cacheTag = 'mpwalletsystem_walletnotification';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpwalletsystem_walletnotification';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWalletSystem\Model\ResourceModel\WalletNotification::class);
    }

    /**
     * Load object data
     *
     * @param  int|null $id
     * @param  string   $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteWalletNotification();
        }
        return parent::load($id, $field);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param  int $id
     * @return \Webkul\WalletNotification\Api\Data\WalletNotificationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
