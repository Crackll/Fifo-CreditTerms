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
use Webkul\MpWalletSystem\Model\WalletrecordFactory;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Webkul\MpWalletSystem\Model\WalletUpdateData;

/**
 * Webkul MpWalletSystem Controller
 */
class Singleaddamount extends WalletController
{
    /**
     * @var Webkul\MpWalletSystem\Model\WalletrecordFactory
     */
    protected $walletrecord;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WallettransactionFactory
     */
    protected $walletTransaction;

    /**
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    protected $walletHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Mail
     */
    protected $mailHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    protected $walletUpdate;

    /**
     * Initialize dependencies
     *
     * @param ActionContext                          $context
     * @param WalletrecordFactory                    $walletrecord
     * @param WallettransactionFactory               $transactionFactory
     * @param WebkulWalletsystemHelperData           $walletHelper
     * @param MagentoFrameworkStdlibDateTimeDateTime $date
     * @param WebkulWalletsystemHelperMail           $mailHelper
     * @param WalletUpdateData                       $walletUpdate
     */
    public function __construct(
        Action\Context $context,
        WalletrecordFactory $walletrecord,
        WallettransactionFactory $transactionFactory,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        WalletUpdateData $walletUpdate
    ) {
        $this->walletrecord = $walletrecord;
        $this->walletTransaction = $transactionFactory;
        $this->walletHelper = $walletHelper;
        $this->date = $date;
        $this->mailHelper = $mailHelper;
        $this->walletUpdate = $walletUpdate;
        parent::__construct($context);
    }

    /**
     * Controller Execute function
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $successCounter = 0;
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $walletTransaction  = $this->walletTransaction->create();
        if (array_key_exists('customerid', $params)
            && $params['customerid'] != ''
        ) {
            if (array_key_exists('walletamount', $params)
                && $params['walletamount']!= ''
                && $params['walletamount'] > 0
            ) {
                $currencycode = $this->walletHelper->getBaseCurrencyCode();
                $params['curr_code'] = $currencycode;
                $params['curr_amount'] = $params['walletamount'];
                $params['sender_id'] = 0;
                $params['sender_type'] = $walletTransaction::ADMIN_TRANSFER_TYPE;
                $params['order_id'] = 0;
                $params['status'] = $walletTransaction::WALLET_TRANS_STATE_APPROVE;
                $params['increment_id'] = '';
                $params['customer_id'] = $customerId = $params['customerid'];
                $totalAmount = 0;
                $remainingAmount = 0;
                $params['walletnote'] = $this->walletHelper->validateScriptTag($params['walletnote']);
                if ($params['walletnote']=='') {
                    $params['walletnote'] = __('Amount %1ed by Admin', $params['walletactiontype']);
                }
                if ($params['walletactiontype']==$walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                    $result = $this->walletUpdate->debitAmount($customerId, $params);
                } else {
                    $result = $this->walletUpdate->creditAmount($customerId, $params);
                }
                if (array_key_exists('success', $result)) {
                    $this->messageManager->addSuccess(
                        __("Amount is successfully updated in customer's wallet.")
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Please Enter a valid amount to add.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Please select Customers to add amount.')
            );
        }
        return $resultRedirect->setPath('mpwalletsystem/wallet/addamount');
    }
}
