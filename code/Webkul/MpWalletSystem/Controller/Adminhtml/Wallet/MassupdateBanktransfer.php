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
use Webkul\MpWalletSystem;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpWalletSystem\Model\Wallettransaction;

/**
 * Webkul MpWalletSystem Controller
 */
class MassupdateBanktransfer extends WalletController
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
     * @param Action\Context                                                      $context
     * @param Filter                                                              $filter
     * @param Walletsyste\Api\WalletTransactionRepositoryInterface                $creditRuleRepository
     * @param Walletsyste\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        \Webkul\MpWalletSystem\Helper\Mail $mailHelper,
        Filter $filter,
        MpWalletSystem\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->mailHelper = $mailHelper;
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
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $alreadyCancelled = 0;
            $approve = 0;
            if (isset($data['selected'])) {
                $selected = count($data['selected']);
            } else {
                $selected = __("All Selected");
            }

            $status = Wallettransaction::WALLET_TRANS_STATE_APPROVE;
            $cancelStatus = Wallettransaction::WALLET_TRANS_STATE_CANCEL;
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $entityIds = $collection->getAllIds();
            if (!empty($entityIds)) {
                $coditionArr = [];
                foreach ($entityIds as $key => $id) {
                    $rowData = $this->collectionFactory->create()
                        ->addFieldToFilter('entity_id', $id);
                    if ($rowData->getFirstItem()->getStatus() == $cancelStatus) {
                        $alreadyCancelled++;
                        continue;
                    }
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                    $approve++;
                }
                if (count($coditionArr)) {
                    $coditionData = implode(' AND ', $coditionArr);
                    $coditionData = $coditionData." AND `status`!=".$cancelStatus;

                    $creditRuleCollection = $this->collectionFactory->create();
                    $creditRuleCollection->setTableRecords(
                        $coditionData,
                        ['status' => $status]
                    );
                }

                /**
                 * Send mail to all approved transactions
                 *
                 * @param wallet transfer collection$collection
                 **/
                $this->mailHelper->sendCustomerBulkTransferApproveMail($collection);
                if ($approve) {
                    $this->messageManager->addSuccess(
                        __(
                            '%1 record(s) successfully updated.',
                            $approve
                        )
                    );
                }

                if ($alreadyCancelled) {
                    $this->messageManager->addWarning(
                        __(
                            "You can not approve already cancelled transaction"
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
}
