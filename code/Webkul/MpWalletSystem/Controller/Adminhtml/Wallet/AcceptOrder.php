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

namespace Webkul\MpWalletSystem\Controller\Adminhtml\Wallet;

use Webkul\MpWalletSystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Sales\Model\Order;

/**
 * Webkul MpWalletSystem Controller
 */
class AcceptOrder extends WalletController
{
    /**
     * Initialize dependencies
     *
     * @param Action\Context                              $context
     * @param Order                                       $order
     * @param \Magento\Framework\App\RequestInterface     $httpRequest
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Webkul\MpWalletSystem\Helper\Mail          $mailHelper
     */
    public function __construct(
        Action\Context $context,
        Order $order,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper
    ) {
        $this->order = $order;
        $this->mailHelper = $mailHelper;
        $this->httpRequest = $httpRequest;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * Controller's execute function
     *
     * @return redirect
     */
    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->order->load($orderId);
            $this->mailHelper->checkAndUpdateWalletAmount($order);
            $this->messageManager->addSuccess(__('Wallet Amount Updated'));
            $order->setTotalPaid($order->getGrandTotal());
            $orderState = Order::STATE_COMPLETE;
            $order->setState($orderState)->setStatus(Order::STATE_COMPLETE);
            $order->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something Went Wrong'));
        }
        $currentUrl = $this->httpRequest->getServer('HTTP_REFERER');
        $result = $this->resultFactory->create($this->resultFactory::TYPE_REDIRECT);
        return $result->setUrl($currentUrl);
    }
}
