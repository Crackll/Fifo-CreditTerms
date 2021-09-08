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

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Webkul\MpWalletSystem\Model\Walletcreditrules;

/**
 * Webkul MpWalletSystem Model Class
 */
class PriceType extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $retrunArray = [
            Walletcreditrules::WALLET_CREDIT_CONFIG_AMOUNT_TYPE_FIXED => __('Fixed'),
            Walletcreditrules::WALLET_CREDIT_CONFIG_AMOUNT_TYPE_PERCENT => __('Percent')
        ];
        return $retrunArray;
    }
}
