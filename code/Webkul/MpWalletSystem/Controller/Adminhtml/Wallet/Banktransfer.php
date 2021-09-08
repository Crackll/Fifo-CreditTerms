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
class Banktransfer extends WalletController
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
        $walletTransactionModel = $this->walletTransaction->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (is_array($params) && array_key_exists('entity_id', $params)
            && $params['entity_id'] != ''
        ) {
            if ($this->walletTransaction->create()
                                        ->getCollection()
                                        ->addFieldToFilter('entity_id', $params['entity_id'])
                                        ->getFirstItem()->getStatus() == 2
            ) {
                $this->messageManager->addWarning(
                    __(
                        "You can not approve already cancelled transaction"
                    )
                );
                return $resultRedirect->setPath(
                    'mpwalletsystem/wallet/bankdetails'
                );
            }
            $condition = "`entity_id`=".$params['entity_id'];
            $condition = $condition." AND `status`!=".$walletTransactionModel::WALLET_TRANS_STATE_CANCEL;

            $this->walletTransaction->create()->getCollection()->setTableRecords(
                $condition,
                ['status' => $walletTransactionModel::WALLET_TRANS_STATE_APPROVE]
            );
            $sendMessageCollection = $this->walletTransaction->create()->getCollection()
                ->addFieldToFilter('entity_id', $params['entity_id']);
            $this->mailHelper->sendCustomerBulkTransferApproveMail($sendMessageCollection);
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
}
