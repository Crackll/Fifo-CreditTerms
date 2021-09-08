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

use Magento\Framework\Exception\LocalizedException;

/**
 * Webkul MpWalletSystem Model Class
 */
class Cart
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    private $walletHelper;
    
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $orderRegistry;
    
    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletsystem\Helper\Data   $walletHelper
     * @param \Magento\Checkout\Model\Session    $checkoutSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry    $registry
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->walletHelper = $walletHelper;
        $this->quote = $checkoutSession->getQuote();
        $this->request = $request;
        $this->orderRegistry = $registry;
    }

    /**
     * Before plugin of addProduct function
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param array $productInfo
     * @param array $requestInfo
     * @return array
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        $params = $this->request->getParams();
        $flag = 0;
        $productId = 0;
        $items = [];
        $walletProductId = $this->walletHelper->getWalletProductId();
        if (array_key_exists('product', $params)) {
            $productId = $params['product'];
        } elseif (array_key_exists('order_id', $params)) {
            $order = $this->orderRegistry->registry('current_order');
            $items = $order->getItemsCollection();
        }
        $quote = $this->quote;
        $cartData = $quote->getAllItems();
        if ($productId) {
            if ($walletProductId == $productId) {
                $flag = 1;
            }
            if (!empty($cartData)) {
                foreach ($cartData as $item) {
                    $itemProductId = $item->getProductId();
                    $this->checkProductId($itemProductId, $walletProductId, $flag);
                }
            }
        } elseif (!empty($items)) {
            $walletInOrder = $this->checkIfOrderHaveWalletProduct($items, $walletProductId);
            if (!empty($cartData)) {
                foreach ($cartData as $item) {
                    $itemProductId = $item->getProductId();
                    $this->checkProductIdInOrder($itemProductId, $walletProductId, $walletInOrder);
                }
            }
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * Check if order have wallet product
     *
     * @param array $items
     * @param int $this->walletProductId
     * @return void
     */
    public function checkIfOrderHaveWalletProduct($items, $walletProductId)
    {
        foreach ($items as $item) {
            $productId = $item->getproduct()->getId();
            if ($productId == $walletProductId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check wallet product id
     *
     * @param int $itemProductId
     * @param int $walletProductId
     * @param int $flag
     */
    public function checkProductId($itemProductId, $walletProductId, $flag)
    {
        if ($walletProductId == $itemProductId) {
            if ($flag != 1) {
                throw new LocalizedException(__('You can not add other product with wallet product'));
            }
        } else {
            if ($flag == 1) {
                throw new LocalizedException(__('You can not add wallet product with other product'));
            }
        }
    }

    /**
     * Check wallet product id in order
     *
     * @param int $itemProductId
     * @param int $walletProductId
     * @param object $walletInOrder
     */
    public function checkProductIdInOrder($itemProductId, $walletProductId, $walletInOrder)
    {
        if ($walletProductId == $itemProductId) {
            if (!$walletInOrder) {
                throw new LocalizedException(__('You can not add other product with wallet product'));
            }
        } else {
            if ($walletInOrder) {
                throw new LocalizedException(__('You can not add wallet product with other product'));
            }
        }
    }
}
