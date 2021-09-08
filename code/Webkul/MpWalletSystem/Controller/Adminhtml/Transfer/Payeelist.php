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

namespace Webkul\MpWalletSystem\Controller\Adminhtml\Transfer;

use Webkul\MpWalletSystem\Controller\Adminhtml\Transfer as TransferController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Webkul MpWalletSystem Controller
 */
class Payeelist extends TransferController
{
    /**
     * Initialize dependencies
     *
     * @param Action\Context                                         $context
     * @param \Webkul\MpWalletSystem\Helper\SplitPayments            $splitPaymentHelper
     * @param \Webkul\MpWalletSystem\Model\WalletNotificationFactory $walletNotification
     */
    public function __construct(
        Action\Context $context,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPaymentHelper,
        \Webkul\MpWalletSystem\Model\WalletNotificationFactory $walletNotification
    ) {
        $this->splitPaymentHelper = $splitPaymentHelper;
        $this->walletNotification = $walletNotification;
        parent::__construct($context);
    }

    /**
     * Controller Execute function
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpWalletSystem::walletpayee');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketplace Wallet System Payee Details'));
        $resultPage->addBreadcrumb(
            __('Marketplace Wallet System Payee Details'),
            __('Marketplace Wallet System Payee Details')
        );
        $notification = $this->walletNotification->create();
        $notifications = $notification->getCollection();
        foreach ($notifications->getItems() as $notification) {
            $notification->setPayeeCounter(0);
            $this->splitPaymentHelper->commitMethod($notification);
        }
        $notifications->save();
        return $resultPage;
    }
}
