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
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;

/**
 * Webkul MpWalletSystem Controller
 */
class Massaddamount extends WalletController
{
    /**
     * @var Webkul\MpWalletSystem\Model\WalletrecordFactory
     */
    private $walletrecord;

    /**
     * @var Webkul\MpWalletSystem\Model\WallettransactionFactory
     */
    private $walletTransaction;

    /**
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    private $walletHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Mail
     */
    private $mailHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    private $walletUpdate;
    
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

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
        WalletUpdateData $walletUpdate,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->walletrecord = $walletrecord;
        $this->walletTransaction = $transactionFactory;
        $this->walletHelper = $walletHelper;
        $this->date = $date;
        $this->mailHelper = $mailHelper;
        $this->walletUpdate = $walletUpdate;
        $this->jsonDecoder = $jsonDecoder;
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
        if (array_key_exists('wkcustomerids', $params) && $params['wkcustomerids'] != '') {
            if (array_key_exists('walletamount', $params)
                && $params['walletamount']!= ''
                && $params['walletamount'] > 0
            ) {
                $customerIds = array_flip($this->jsonDecoder->decode($params['wkcustomerids']));
                $currencycode = $this->walletHelper->getBaseCurrencyCode();
                $params['curr_code'] = $currencycode;
                $params['curr_amount'] = $params['walletamount'];
                $params['sender_id'] = 0;
                $params['sender_type'] = $walletTransaction::ADMIN_TRANSFER_TYPE;
                $params['order_id'] = 0;
                $params['status'] = $walletTransaction::WALLET_TRANS_STATE_APPROVE;
                $params['increment_id'] = '';
                $params['walletnote'] = $this->walletHelper->validateScriptTag($params['walletnote']);
                if ($params['walletnote']=='') {
                    $params['walletnote'] = __('Amount %1ed by Admin', $params['walletactiontype']);
                }
                foreach ($customerIds as $customerId) {
                    if ($params['walletactiontype']==$walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                        $params['customer_id'] = $customerId;
                        $result = $this->walletUpdate->debitAmount($customerId, $params);
                    } else {
                        $params['customer_id'] = $customerId;
                        $result = $this->walletUpdate->creditAmount($customerId, $params);
                    }
                    if (array_key_exists('success', $result)) {
                        $successCounter++;
                    }
                }

                if ($successCounter > 0) {
                    $this->messageManager->addSuccess(
                        __("Total of %1 Customer(s) wallet are updated", $successCounter)
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
