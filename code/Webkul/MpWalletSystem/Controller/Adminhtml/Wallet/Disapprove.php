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
class Disapprove extends WalletController
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
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry,
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
        $this->websiteRepositiry = $websiteRepositiry;
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

        $walletTransactionModel = $this->walletTransaction->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (is_array($params) && array_key_exists('entity_id', $params)
            && $params['entity_id'] != ''
        ) {
            $condition = "`entity_id`=".$params['entity_id'];
            $statusCollection = $this->walletTransaction->create()->getCollection()
                ->addFieldToFilter('entity_id', $params['entity_id']);
            foreach ($statusCollection as $status) {
                $status = $status->getStatus();
            }
            if ($status == $walletTransactionModel::WALLET_TRANS_STATE_CANCEL) {
                $this->messageManager->addSuccess(
                    __('Transaction status is updated.')
                );
                return $resultRedirect->setPath(
                    'mpwalletsystem/wallet/view',
                    ['entity_id'=>$params['entity_id']]
                );
            } elseif ($status == $walletTransactionModel::WALLET_TRANS_STATE_APPROVE) {
                $this->messageManager->addWarning(
                    __('Already approved transactions cannot be cancelled.')
                );
                return $resultRedirect->setPath(
                    'mpwalletsystem/wallet/bankdetails',
                    ['sender_type'=>$walletTransactionModel::CUSTOMER_TRANSFER_BANK_TYPE]
                );
            }
            $this->walletTransaction->create()->getCollection()->setTableRecords(
                $condition,
                ['status' => $walletTransactionModel::WALLET_TRANS_STATE_CANCEL]
            );
            $this->creditAmountToCustomerWallet($params['entity_id']);
            $this->messageManager->addSuccess(
                __('Transaction status is updated.')
            );
        } else {
            $this->messageManager->addError(
                __('Something went wrong, please try again.')
            );
            return $resultRedirect->setPath(
                'mpwalletsystem/wallet/index'
            );
        }
        return $resultRedirect->setPath(
            'mpwalletsystem/wallet/view',
            ['entity_id'=>$params['entity_id']]
        );
    }

    /**
     * Credit amount to customer wallet
     *
     * @param int $txnId
     * @return void
     */
    public function creditAmountToCustomerWallet($txnId)
    {
        $walletTransaction  = $this->walletTransaction->create();
        $txnDetails = $walletTransaction->getCollection()
            ->addFieldToFilter('entity_id', $txnId);
        foreach ($txnDetails as $txnDetails) {
            $txn = $txnDetails;
        }
        $amount = $txn->getAmount();
        $customerId = $txn->getCustomerId();

        $baseUrl = $this->websiteRepositiry->getDefault()->getDefaultStore()->getBaseUrl();
        $url = $baseUrl."mpwalletsystem/index/view/entity_id/".$txnId;
        $link = "<a href='".$url."'> #".$txnId."</a>";

        $currencycode = $this->walletHelper->getBaseCurrencyCode();
        $params['curr_code'] = $currencycode;
        $params['walletactiontype'] = "credit";
        $params['curr_amount'] = $amount;
        $params['walletamount'] = $amount;
        $params['sender_id'] = 0;
        $params['sender_type'] = $walletTransaction::ADMIN_TRANSFER_TYPE;
        $params['order_id'] = 0;
        $params['status'] = $walletTransaction::WALLET_TRANS_STATE_APPROVE;
        $params['increment_id'] = '';
        $params['customer_id'] = $customerId;
        $params['walletnote'] = __("Request To transfer amount to Bank is cancelled").$link;
        $result = $this->walletUpdate->creditAmount($customerId, $params);
    }
}
