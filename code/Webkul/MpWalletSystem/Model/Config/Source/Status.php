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

namespace Webkul\MpWalletSystem\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\MpWalletSystem\Model\Walletcreditrules;

/**
 * Webkul MpWalletSystem Model Class
 */
class Status implements OptionSourceInterface
{
    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            Walletcreditrules::WALLET_CREDIT_RULE_STATUS_ENABLE => __('Enabled'),
            Walletcreditrules::WALLET_CREDIT_RULE_STATUS_DISABLE => __('Disabled')
        ];
    }
}
