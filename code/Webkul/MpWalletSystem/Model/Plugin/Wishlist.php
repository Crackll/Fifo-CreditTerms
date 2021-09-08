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
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul MpWalletSystem Model Class
 */
class Wishlist
{
    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data          $walletHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\App\Request\Http         $request
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Framework\Registry                 $registry
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->walletHelper = $walletHelper;
        $this->messageManager = $messageManager;
        $this->quote = $checkoutSession->getQuote();
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->orderRegistry = $registry;
    }

    /**
     * Before plugin of execute function
     *
     * @param \Magento\Wishlist\Controller\Index\Cart $subject
     * @return void
     */
    public function beforeExecute(
        \Magento\Wishlist\Controller\Index\Cart $subject
    ) {
        $params = $this->request->getParams();
        $flag = 0;
        $productId = 0;
        $items = [];
        $walletProductId = $this->walletHelper->getWalletProductId();

        $quote = $this->quote;
        $cartData = $quote->getAllItems();
        if (!empty($cartData)) {
            foreach ($cartData as $item) {
                if ($item->getProductId() == $walletProductId) {
                    $flag = true;
                }
            }
        }
        if ($flag) {
            $this->messageManager->addError(__('You can not add other product with wallet product'));
            unset($params['item']);
            return $this->request->setPostValue($params);
        }
    }
}
