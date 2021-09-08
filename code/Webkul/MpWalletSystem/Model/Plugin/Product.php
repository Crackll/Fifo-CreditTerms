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

namespace Webkul\MpWalletSystem\Model\Plugin;

/**
 * Webkul MpWalletSystem Model Class
 */
class Product
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    private $walletHelper;
    
    /**
     * @var Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data $walletHelper
     * @param \Magento\Framework\App\State       $appState
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Magento\Framework\App\State $appState
    ) {
        $this->walletHelper = $walletHelper;
        $this->appState = $appState;
    }

    /**
     * Around plugin of addAttributeToSelect function
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param \Closure                                                $proceed
     * @param object                                                  $attribute
     * @param boolean                                                 $joinType
     */
    public function aroundAddAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        \Closure $proceed,
        $attribute,
        $joinType = false
    ) {
        $appState = $this->appState;
        $areCode = $appState->getAreaCode();
        $walletSku = $this->walletHelper::WALLET_PRODUCT_SKU;
        $result = $proceed($attribute, $joinType = false);
        $code = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
        if ($appState->getAreaCode() == $code) {
            $result->addFieldToFilter('sku', ['neq' => $walletSku]);
        }

        return $result;
    }
}
