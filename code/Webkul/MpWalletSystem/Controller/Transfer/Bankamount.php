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

namespace Webkul\MpWalletSystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Webkul\MpWalletSystem\Model\Wallettransaction;

/**
 * Webkul MpWalletSystem Controller
 */
class Bankamount extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Mail
     */
    protected $walletHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    protected $walletUpdate;
    
    /**
     * Initialize depndencies
     *
     * @param Context                                            $context
     * @param PageFactory                                        $resultPageFactory
     * @param \Webkul\MpWalletSystem\Helper\Data                 $walletHelper
     * @param \Webkul\MpWalletSystem\Model\WalletNotification    $walletNotification
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param WalletUpdateData                                   $walletUpdate
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Webkul\MpWalletSystem\Model\WalletNotification $walletNotification,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        WalletUpdateData $walletUpdate
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletNotification = $walletNotification;
        $this->walletHelper = $walletHelper;
        $this->scopeConfig = $scopeConfig;
        $this->walletUpdate = $walletUpdate;
        parent::__construct($context);
    }
    
    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->validateParams($params);
        return $this->resultRedirectFactory->create()->setPath(
            'mpwalletsystem/index/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Validate params
     *
     * @param array $params
     * @return bool
     */
    protected function validateParams($params)
    {
        if (!empty($params) && is_array($params)) {
            if (array_key_exists('customer_id', $params) && $params['customer_id']!='') {
                if (array_key_exists('amount', $params)
                    && $params['amount']!=''
                    && array_key_exists('bank_details', $params)
                    && $params['bank_details']!=''
                    && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['walletnote'])
                    && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['bank_details'])
                ) {
                    $params['walletnote'] = preg_replace(
                        '#<script(.*?)>(.*?)</script>#is',
                        '',
                        $params['walletnote']
                    );
                    $params['bank_details'] = preg_replace(
                        '#<script(.*?)>(.*?)</script>#is',
                        '',
                        $params['bank_details']
                    );
                    $baseCurrencyCode = $this->walletHelper->getBaseCurrencyCode();
                    $currencycode = $this->walletHelper->getCurrentCurrencyCode();
                    $amount = $params['amount'];
                    $baseAmount = $this->walletHelper->getwkconvertCurrency(
                        $currencycode,
                        $baseCurrencyCode,
                        $amount
                    );
                    $customerId = $params['customer_id'];
                    $params['curr_code'] = $currencycode;
                    $params['curr_amount'] = $params['amount'];
                    $params['order_id'] = 0;
                    $params['status'] = Wallettransaction::WALLET_TRANS_STATE_PENDING;
                    $params['increment_id'] = '';
                    $params['walletamount'] = $baseAmount;
                    $params['walletactiontype'] = 'debit';
                    $params['sender_id'] = 0;
                    $params['sender_type'] = Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE;
                    $params['transfer_to_bank'] = 1;
                    if ($params['walletnote']=='') {
                        $params['walletnote'] = __(
                            '%1, Amount is transferred by customer to bank account',
                            $params['amount']
                        );
                    }
                    $result = $this->walletUpdate->debitAmount($customerId, $params);
                    if (is_array($result) && array_key_exists('success', $result)) {
                        $this->setNotificationMessageForAdmin();
                        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                        if ($this->scopeConfig->getValue(
                            'mpwalletsystem/message_after_request/show_message',
                            $storeScope
                        )
                        ) {
                            $message = $this->scopeConfig->getValue(
                                'mpwalletsystem/message_after_request/message_content',
                                $storeScope
                            );
                            $this->messageManager->addSuccess(__($message));
                        } else {
                            $this->messageManager->addSuccess(__("Amount transfer request has been sent!"));
                        }
                    }
                } else {
                    $this->messageManager->addError(__("Something went wrong, please try again"));
                }
            } else {
                $this->messageManager->addError(__("Something went wrong, please try again"));
            }
        } else {
            $this->messageManager->addError(__("Something went wrong, please try again"));
        }
    }

    /**
     * Set notification message for admin
     *
     * @return void
     */
    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->walletNotification->setBanktransferCounter(1);
            $this->walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setBanktransferCounter($notification->getBanktransferCounter()+1);
            }
        }
        $notificationModel->save();
    }
}
