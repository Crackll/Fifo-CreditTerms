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
interface WalletPayeeInterface
{
    const ENTITY_ID = 'entity_id';
    
    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId();
    
    /**
     * Set Entity ID
     *
     * @param  int $id
     * @return \Webkul\MpWalletSystem\Api\Data\WalletPayeeInterface
     */
    public function setEntityId($id);
}
