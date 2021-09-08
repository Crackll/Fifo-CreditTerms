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

namespace Webkul\MpWalletSystem\Model\Plugin\Checks;

/**
 * Webkul MpWalletSystem Model Class
 */
class ZeroTotal
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    private $walletHelper;

    /**
     * Initialize dependecies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data $walletHelper
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $walletHelper
    ) {
        $this->walletHelper = $walletHelper;
    }

    /**
     * Initialize dependencies
     *
     * @param \Magento\Payment\Model\Checks\ZeroTotal $subject
     * @param bool $result
     * @return void
     */
    public function afterIsApplicable(
        \Magento\Payment\Model\Checks\ZeroTotal $subject,
        $result
    ) {
        if (!$result) {
            return $this->walletHelper->getPaymentisEnabled();
        }
        return $result;
    }
}
