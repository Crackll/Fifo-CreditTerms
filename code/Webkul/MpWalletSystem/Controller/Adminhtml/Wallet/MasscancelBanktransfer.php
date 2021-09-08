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
use Magento\Backend\App\Action;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpWalletSystem\Model\Wallettransaction;

/**
 * Webkul MpWalletSystem Controller
 */
class MasscancelBanktransfer extends WalletController
{
    /**
     * @var Filter
     */
    protected $filter;
    
    /**
     * @var MpWalletSystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Initialize dependencies
     *
     * @param Action\Context                                                                 $context
     * @param Filter                                                                         $filter
     * @param \Magento\Store\Api\WebsiteRepositoryInterface                                  $websiteRepositiry
     * @param WallettransactionFactory                                                       $transactionFactory
     * @param \Webkul\MpWalletSystem\Controller\Adminhtml\Wallet\Disapprove                  $disapprove
     * @param \Webkul\MpWalletSystem\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory
     * @param WalletUpdateData                                                               $walletUpdate
     * @param \Webkul\MpWalletSystem\Helper\Data                                             $helper
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry,
        WallettransactionFactory $transactionFactory,
        \Webkul\MpWalletSystem\Controller\Adminhtml\Wallet\Disapprove $disapprove,
        \Webkul\MpWalletSystem\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory,
        WalletUpdateData $walletUpdate,
        \Webkul\MpWalletSystem\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->websiteRepositiry = $websiteRepositiry;
        $this->disapprove = $disapprove;
        $this->walletHelper = $helper;
        $this->filter = $filter;
        $this->walletUpdate = $walletUpdate;
        $this->walletTransaction = $transactionFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Mass Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $alreadyApproved = 0;
            $cancelled = 0;
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $this->refundAmountToWallet($this->getRequest()->getParams());
            if (isset($data['selected'])) {
                $selected = count($data['selected']);
            } else {
                $selected = __("All Selected");
            }

            $status = Wallettransaction::WALLET_TRANS_STATE_CANCEL;
            $approvedStatus = Wallettransaction::WALLET_TRANS_STATE_APPROVE;
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $entityIds = $collection->addFieldToFilter('sender_type', Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE)
                ->getAllIds();
            if (!empty($entityIds)) {
                $coditionArr = [];
                foreach ($entityIds as $key => $id) {
                    $rowData = $this->collectionFactory->create()
                        ->addFieldToFilter('entity_id', $id);
                    if ($rowData->getFirstItem()->getStatus() == $approvedStatus) {
                        $alreadyApproved++;
                        continue;
                    }
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                    $cancelled++;
                }
                if (count($coditionArr)) {
                    $coditionData = implode(' AND ', $coditionArr);
                    $creditRuleCollection = $this->collectionFactory->create();
                    $creditRuleCollection->setTableRecords(
                        $coditionData,
                        ['status' => $status]
                    );
                }

                if ($alreadyApproved) {
                    $this->messageManager->addWarning(
                        __('You cannot cancel already approved transactions')
                    );
                }
                if ($cancelled) {
                    $this->messageManager->addSuccess(
                        __(
                            '%1 record(s) successfully updated.',
                            $cancelled
                        )
                    );
                }
            }
            return $resultRedirect->setPath(
                '*/*/bankdetails',
                ['sender_type'=>Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __($e->getMessage())
            );
        }
        return $resultRedirect->setPath(
            '*/*/bankdetails',
            ['sender_type'=>Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE]
        );
    }

    /**
     * Refund amount to wallet
     *
     * @param array $params
     * @return void
     */
    public function refundAmountToWallet($params)
    {
        if (isset($params['selected'])) {
            $ids = $params['selected'];
            $this->refundSelectedTransactions($ids);
        } else {
            $this->refundAllPendingTransactions();
        }
        return true;
    }

    /**
     * Refund all pending transactions
     *
     * @return void
     */
    public function refundAllPendingTransactions()
    {
        $txns = $this->walletTransaction->create()->getCollection()
            ->addFieldToFilter('status', Wallettransaction::WALLET_TRANS_STATE_PENDING);
        foreach ($txns as $txn) {
            $this->creditAmountToCustomerWallet($txn->getEntityId());
        }
    }

    /**
     * Refund selected transactions
     *
     * @param array $ids
     * @return void
     */
    public function refundSelectedTransactions($ids)
    {
        $txns = $this->walletTransaction->create()->getCollection()
            ->addFieldToFilter('entity_id', ['in'=>$ids])
            ->addFieldToFilter('status', Wallettransaction::WALLET_TRANS_STATE_PENDING);
        foreach ($txns as $txn) {
            $this->creditAmountToCustomerWallet($txn->getEntityId());
        }
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
        $baseUrl = $this->websiteRepositiry->getDefault()->getDefaultStore()->getBaseUrl();
        $url = $baseUrl."mpwalletsystem/index/view/entity_id/".$txnId;
        $link = "<a href='".$url."'> #".$txnId."</a>";
        $amount = $txn->getAmount();
        $customerId = $txn->getCustomerId();
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
