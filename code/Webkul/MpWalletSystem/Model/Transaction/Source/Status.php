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

namespace Webkul\MpWalletSystem\Model\Transaction\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\MpWalletSystem\Model\Wallettransaction;

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
            Wallettransaction::WALLET_TRANS_STATE_APPROVE => __('Approved'),
            Wallettransaction::WALLET_TRANS_STATE_PENDING=> __('Pending'),
            Wallettransaction::WALLET_TRANS_STATE_CANCEL=> __('Cancelled')
        ];
    }
}
