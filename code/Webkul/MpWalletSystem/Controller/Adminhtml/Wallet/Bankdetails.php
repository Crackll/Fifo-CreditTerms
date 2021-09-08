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
use Magento\Backend\App\Action\Context;

/**
 * Webkul MpWalletSystem Controller
 */
class Bankdetails extends WalletController
{

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context                    $context
     * @param \Webkul\MpWalletSystem\Helper\SplitPayments            $splitPaymentHelper
     * @param \Magento\Customer\Model\CustomerFactory                $customerModel
     * @param \Webkul\MpWalletSystem\Model\WalletNotificationFactory $walletNotification
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPaymentHelper,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Webkul\MpWalletSystem\Model\WalletNotificationFactory $walletNotification
    ) {
        parent::__construct($context);
        $this->splitPaymentHelper = $splitPaymentHelper;
        $this->customerModel = $customerModel;
        $this->walletNotification = $walletNotification;
    }

    /**
     * Controller Execute function
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpWalletSystem::banktransfer');
        $resultPage->getConfig()->getTitle()
            ->prepend(__('Bank Transfer Details'));
        $resultPage->addBreadcrumb(
            __("Bank Transfer Details"),
            __("Bank Transfer Details")
        );
        $notification = $this->walletNotification->create();
        $notifications = $notification->getCollection();
        foreach ($notifications->getItems() as $notification) {
            $notification->setBanktransferCounter(0);
            $this->splitPaymentHelper->commitMethod($notification);
        }
        $notifications->save();
        return $resultPage;
    }
}
