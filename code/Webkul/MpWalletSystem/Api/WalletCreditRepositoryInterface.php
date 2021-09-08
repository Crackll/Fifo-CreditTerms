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

namespace Webkul\MpWalletSystem\Api;

/**
 * Webkul MpWalletSystem Interface
 */
interface WalletCreditRepositoryInterface
{
    /**
     * Create or update a credit rule.
     *
     * @param  \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface $creditRule
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function save(\Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface $creditRule);

    /**
     * Get creditRule by creditRule Id
     *
     * @param  int $entityId
     * @return \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     */
    public function getById($entityId);

    /**
     * Delete creditRule.
     *
     * @param  \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface $creditRule
     * @return bool true on success
     */
    public function delete(\Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface $creditRule);

    /**
     * Delete creditRule by ID.
     *
     * @param  int $entityId
     * @return bool true on success
     */
    public function deleteById($entityId);
}
