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
use Webkul\MpWalletSystem\Helper\Data as WalletHelper;
use Webkul\MpWalletSystem\Helper\Mail as WalletMail;
use Webkul\MpWalletSystem\Model\WalletTransferData;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Webkul\MpWalletSystem\Model\Wallettransaction;
use \Magento\Customer\Model\Session;

/**
 * Webkul MpWalletSystem Controller
 */
class Sendamount extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    protected $walletHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Helper\Mail
     */
    protected $walletMail;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletTransferData
     */
    protected $waletTransfer;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    protected $walletUpdate;
    
    /**
     * @var Encryptor
     */
    private $encryptor;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Initialize dependencies
     *
     * @param Context            $context
     * @param PageFactory        $resultPageFactory
     * @param WalletHelper       $walletHelper
     * @param WalletMail         $walletMail
     * @param WalletTransferData $walletSession
     * @param Encryptor          $encryptor
     * @param WalletUpdateData   $walletUpdate
     * @param Session            $session
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WalletHelper $walletHelper,
        WalletMail $walletMail,
        WalletTransferData $walletSession,
        Encryptor $encryptor,
        WalletUpdateData $walletUpdate,
        Session $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->walletMail = $walletMail;
        $this->waletTransfer = $walletSession;
        $this->encryptor = $encryptor;
        $this->walletUpdate = $walletUpdate;
        $this->customerSession = $session;
        parent::__construct($context);
    }
    
    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $error = $this->validateParams($params);
            if ($error) {
                $params['sender_id'] = $this->customerSession->getParams()['sender_id'];
                $params['reciever_id'] = $this->customerSession->getParams()['reciever_id'];
                $params['amount'] = $this->customerSession->getParams()['amount'];
            }
            if (!isset($params['walletnote'])) {
                $params['walletnote'] = "";
            }
            $walletHelper = $this->walletHelper;
            if (!$walletHelper->getTransferValidationEnable()) {
                $params['curr_code'] = $walletHelper->getCurrentCurrencyCode();
                $fromCurrency = $walletHelper->getCurrentCurrencyCode();
                $toCurrency = $walletHelper->getBaseCurrencyCode();
                $amount = $params['amount'];
                $transferAmount = $walletHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $amount);
                $totalAmount = $walletHelper->getWalletTotalAmount(0);
                if ($transferAmount <= $totalAmount) {
                    $params['base_amount'] = $transferAmount;
                    $params['curr_amount'] = $params['amount'];
                    $this->sendAmountToWallet($params);
                    $this->deductAmountFromWallet($params);
                    $this->messageManager->addSuccess(__("Amount transferred successfully"));
                } else {
                    $this->messageManager->addError(__("You don't have enough amount in your wallet."));
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpwalletsystem/transfer/index',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $this->waletTransfer->checkAndUpdateSession();
                $walletTransferData = $this->waletTransfer->getWalletTransferDataToSession();
                if ($walletTransferData=='') {
                    $this->throwException("Either code session is expired, or amount is already transferred.");
                }
                $walletCookieArray = $walletHelper->convertStringAccToVersion($walletTransferData, 'decode');
                if ($walletCookieArray['sender_id']==$params['sender_id']
                    && $walletCookieArray['amount']==$params['amount']
                    && $walletCookieArray['reciever_id']==$params['reciever_id']
                ) {
                    if (!$this->encryptor->validateHash($params['code'], $walletCookieArray['code'])) {
                        $this->throwException("Incorrect code");
                    }
                    $params['curr_code'] = $this->walletHelper->getCurrentCurrencyCode();
                    $params['base_amount'] = $params['curr_amount'] = $params['amount'];
                    $params['walletnote'] = $walletCookieArray['walletnote'];
                    $this->sendAmountToWallet($params);
                    $this->deductAmountFromWallet($params);
                    $this->waletTransfer->setWalletTransferDataToSession('');
                    $this->messageManager->addSuccess(__("Amount transferred successfully"));
                } else {
                    $this->throwException("Something went wrong, please try again.");
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            'mpwalletsystem/transfer/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Throws exception
     *
     * @param string $msg
     * @return \LocalizedException
     */
    public function throwException($msg)
    {
        throw new \Magento\Framework\Exception\LocalizedException(__($msg));
    }

    /**
     * Validate params
     *
     * @param array $params
     * @return bool
     */
    public function validateParams($params)
    {
        $error = 0;
        foreach ($params as $paramkey => $paramvalue) {
            switch ($paramkey) {
                case 'sender_id':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'reciever_id':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'code':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'amount':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
            }
        }
        return $error;
    }

    /**
     * Send amount to customer's wallet
     *
     * @param array $params
     * @return void
     */
    public function sendAmountToWallet($params)
    {
        $customerModel = $this->walletHelper->getCustomerByCustomerId($params['sender_id']);
        $senderName = $customerModel->getFirstname()." ".$customerModel->getLastname();
        if ($params['walletnote']=='') {
            $params['walletnote'] = __("Transfer by %1", $senderName);
        }
        $transferAmountData = [
            'customer_id' => $params['reciever_id'],
            'walletamount' => $params['base_amount'],
            'walletactiontype' => Wallettransaction::WALLET_ACTION_TYPE_CREDIT,
            'curr_code' => $params['curr_code'],
            'curr_amount' => $params['curr_amount'],
            'walletnote' => __($params['walletnote']),
            'sender_id' => $params['sender_id'],
            'sender_type' => Wallettransaction::CUSTOMER_TRANSFER_TYPE,
            'order_id' => 0,
            'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => ''
        ];
        $this->walletUpdate->creditAmount($params['reciever_id'], $transferAmountData);
    }

    /**
     * Deduct amount from sender's wallet
     *
     * @param array $params
     * @return void
     */
    public function deductAmountFromWallet($params)
    {
        $customerModel = $this->walletHelper->getCustomerByCustomerId($params['reciever_id']);
        $recieverName = $customerModel->getFirstname()." ".$customerModel->getLastname();
        if ($params['walletnote']=='') {
            $params['walletnote'] = __("Transfer to %1", $recieverName);
        }
        $transferAmountData = [
            'customer_id' => $params['sender_id'],
            'walletamount' => $params['base_amount'],
            'walletactiontype' => Wallettransaction::WALLET_ACTION_TYPE_DEBIT,
            'curr_code' => $params['curr_code'],
            'curr_amount' => $params['curr_amount'],
            'walletnote' => __($params['walletnote']),
            'sender_id' => $params['reciever_id'],
            'sender_type' => Wallettransaction::CUSTOMER_TRANSFER_TYPE,
            'order_id' => 0,
            'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => ''
        ];
        $this->walletUpdate->debitAmount($params['sender_id'], $transferAmountData);
    }
}
