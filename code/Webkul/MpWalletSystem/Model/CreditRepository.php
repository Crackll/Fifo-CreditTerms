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

use Webkul\MpWalletSystem\Model\WalletcreditrulesFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Webkul MpWalletSystem Model Class
 */
class CreditRepository implements \Webkul\MpWalletSystem\Api\WalletCreditRepositoryInterface
{
    /**
     * @var Webkul\MpWalletSystem\Model\WalletcreditrulesFactory
     */
    protected $walletcreditrulesFactory;

    /**
     * Initialize dependencies
     *
     * @param WalletcreditrulesFactory $walletcreditrulesFactory
     */
    public function __construct(
        WalletcreditrulesFactory $walletcreditrulesFactory
    ) {
        $this->walletcreditrulesFactory = $walletcreditrulesFactory;
    }

    /**
     * Save creditRule.
     *
     * @param  Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface $creditRule
     * @return Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface $creditRule)
    {
        $creditRuleModel = null;
        if ($creditRule->getEntityId()) {
            $creditRuleModel = $this->walletcreditrulesFactory->create()->load($creditRule->getEntityId());
        }
        if ($creditRuleModel === null) {
            $creditRuleModel = $this->walletcreditrulesFactory->create();
            $creditRuleModel->addData($creditRule);
        } else {
            $creditRuleModel->addData($creditRule);
        }
        $creditRuleId = $creditRuleModel->save()->getEntityId();
        return $this->walletcreditrulesFactory->create()->load($creditRuleId);
    }
    
    /**
     * Retrieve credit Rule data.
     *
     * @param  int $creditRuleId
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($creditRuleId)
    {
        if (!$creditRuleId) {
            throw new \Magento\Framework\Exception\InputException(__('Credit Rule Id required'));
        }
        $creditRuleModel = $this->walletcreditrulesFactory->create();
        $creditRuleModel->load($creditRuleId);
        if (!$creditRuleModel->getEntityId()) {
            throw new NoSuchEntityException(__('Requested Rule doesn\'t exist'));
        }
        return $creditRuleModel;
    }

    /**
     * Delete credit rule.
     *
     * @param  \Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface $creditRule
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\MpWalletSystem\Api\Data\WalletCreditRuleInterface $creditRule)
    {
        try {
            $creditRuleId = $creditRule->getEntityId();
            $creditRule->delete();
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove credit rule %1', $creditRuleId)
            );
        }
        return true;
    }

    /**
     * Delete credit rule by credit rule ID.
     *
     * @param  int $creditRuleId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($creditRuleId)
    {
        $creditRuleModel = $this->getById($creditRuleId);
        $this->delete($creditRuleModel);
        return true;
    }
}
