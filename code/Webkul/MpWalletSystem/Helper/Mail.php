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

namespace Webkul\MpWalletSystem\Helper;

/**
 * Webkul MpWalletSystem Helper Class
 */
class Mail extends Data
{
    /**
     * @var templateId
     */
    protected $tempId;

    /**
     * Generate template id
     *
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return this
     */
    protected function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template =  $this->transportBuilder->setTemplateIdentifier($this->tempId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $emailTemplateVariables['store_id'],
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo, 'store', $emailTemplateVariables['store_id'])
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

    /**
     * Calls when invoice generated for an order either on create invoice or by capture method
     *
     * @param object $order
     * @return void
     */
    public function checkAndUpdateWalletAmount($order)
    {
        $walletTransaction = $this->walletTransaction->create();
        $orderId = $order->getId();
        if ($orderId) {
            $totalAmount = 0;
            $remainingAmount = 0;
            $orderModel = $this->orderModel
                ->create()
                ->load($orderId);
            $incrementId = $order->getIncrementId();
            $orderItem = $orderModel->getAllItems();
            $productIdArray = [];
            foreach ($orderItem as $value) {
                $productIdArray[] = $value->getProductId();
            }
            $walletProductId = $this->getWalletProductId();
            if (in_array($walletProductId, $productIdArray)) {
                $customerId = $order->getCustomerId();
                $amount = $order->getBaseGrandTotal();
                if ((int)$order->getDiscountAmount()) {
                    $amount += -(int)$order->getDiscountAmount();
                }
                $curr_amount = $this->getwkconvertCurrency(
                    $order->getBaseCurrencyCode(),
                    $order->getOrderCurrencyCode(),
                    $amount
                );
                $action = $walletTransaction::WALLET_ACTION_TYPE_CREDIT;
                    $walletTansactionModel = $this->walletTransaction
                        ->create()
                        ->setCustomerId($customerId)
                        ->setAmount($amount)
                        ->setCurrAmount($curr_amount)
                        ->setStatus(1)
                        ->setCurrencyCode($order->getOrderCurrencyCode())
                        ->setAction($action)
                        ->setTransactionNote("Money added in wallet")
                        ->setOrderId($order->getEntityId())
                        ->setTransactionAt($this->date->gmtDate())
                        ->save();
                    $walletRecordCollection = $this->walletRecordFactory
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'customer_id',
                            ['eq' => $customerId]
                        );
                if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                    $this->updateWalletDataAmount($walletRecordCollection, $amount, $customerId, $incrementId);
                }
            }
        }
    }
    
    /**
     * Update wallet amount
     *
     * @param Collection $walletRecordCollection
     * @param int $amount
     * @param int $customerId
     * @param int $incrementId
     * @return void
     */
    public function updateWalletDataAmount($walletRecordCollection, $amount, $customerId, $incrementId)
    {
        $remainingAmount = 0;
        $totalAmount = 0;
        $walletTransaction = $this->walletTransaction->create();
        if ($walletRecordCollection->getSize()) {
            foreach ($walletRecordCollection as $record) {
                $totalAmount = $record->getTotalAmount();
                $remainingAmount = $record->getRemainingAmount();
                $recordId = $record->getId();
            }
            $data = [
                'total_amount' => $amount + $totalAmount,
                'remaining_amount' => $amount + $remainingAmount,
                'updated_at' => $this->date->gmtDate(),
            ];
            $walletRecordModel = $this->walletRecordFactory
                ->create()
                ->load($recordId)
                ->addData($data);
            $saved = $walletRecordModel->setId($recordId)->save();
        } else {
            $walletRecordModel = $this->walletRecordFactory
                ->create();
            $walletRecordModel->setTotalAmount($amount + $totalAmount)
                ->setCustomerId($customerId)
                ->setRemainingAmount($amount + $remainingAmount)
                ->setUpdatedAt($this->date->gmtDate());
            $saved = $walletRecordModel->save();
        }
        if ($saved->getId() != 0) {
            $date = $this->localeDate->date(new \DateTime($this->date->gmtDate()));
            $formattedDate = $this->date->gmtDate();
            $finalAmount = $amount + $remainingAmount;
            $emailParams = [
                'walletamount' => $this->getformattedPrice($amount),
                'remainingamount' => $this->getformattedPrice($finalAmount),
                'type' => $walletTransaction::ORDER_PLACE_TYPE,
                'action' => $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                'increment_id' => $incrementId,
                'transaction_at' => $formattedDate
            ];
            $store = $this->storeManager->getStore();
            $this->sendMailForTransaction(
                $customerId,
                $emailParams,
                $store
            );
        }
    }

    /**
     * Send transfer code
     *
     * @param array $mailData
     * @return void
     */
    public function sendTransferCode($mailData)
    {
        try {
            $customer = $this->customerModel
                ->create()
                ->load($mailData['customer_id']);
            $emailTempVariables = [];
            $emailTempVariables['customername'] = $customer->getName();
            $emailTempVariables['code'] = $mailData['code'];
            $emailTempVariables['duration'] = $mailData['duration'];
            $emailTempVariables['amount'] = $this->getformattedPrice($mailData['base_amount']);
            $adminEmail= $this->getDefaultTransEmailId();
            $adminUsername = 'Admin';
            $senderInfo = [];
            $receiverInfo = [];
            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $storeId = $this->getWebsiteStoreId($customer);
            if (!$storeId) {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $emailTempVariables['store'] = $this->storeManager->getStore($storeId);
            $emailTempVariables['store_id'] = $storeId;

            $this->tempId = $this->getCustomerAmountTransferOTPTemplateId();
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Sends monthly transacations
     *
     * @param array $mailData
     * @return void
     */
    public function sendMonthlyTransaction($mailData)
    {
        $currency = $this->getBaseCurrencyCode();
        try {
            $customer = $this->customerModel
                ->create()
                ->load($mailData['customer_id']);
            $emailTempVariables = [];
            $emailTempVariables['customername'] = $customer->getName();
            $emailTempVariables['date'] = $mailData['month'].", ".$mailData['year'];
            $emailTempVariables['emailid'] = $customer->getEmail();
            $emailTempVariables['month'] = $mailData['month'];
            $emailTempVariables['year'] = $mailData['year'];
            $emailTempVariables['openingbalance'] = $this->getFormattedPriceAccToCurrency(
                $mailData['openingbalance'],
                2,
                $currency
            );
            $emailTempVariables['closingbalance'] = $this->getFormattedPriceAccToCurrency(
                $mailData['closingbalance'],
                2,
                $currency
            );
            $emailTempVariables['rechargewallet'] = $this->getFormattedPriceAccToCurrency(
                $mailData['rechargewallet'],
                2,
                $currency
            );
            $emailTempVariables['cashbackamount'] = $this->getFormattedPriceAccToCurrency(
                $mailData['cashbackamount'],
                2,
                $currency
            );
            $emailTempVariables['refundamount'] = $this->getFormattedPriceAccToCurrency(
                $mailData['refundamount'],
                2,
                $currency
            );
            $emailTempVariables['admincredit'] = $this->getFormattedPriceAccToCurrency(
                $mailData['admincredit'],
                2,
                $currency
            );
            $emailTempVariables['customercredits'] = $this->getFormattedPriceAccToCurrency(
                $mailData['customercredits'],
                2,
                $currency
            );
            $emailTempVariables['usedwallet'] = $this->getFormattedPriceAccToCurrency(
                $mailData['usedwallet'],
                2,
                $currency
            );
            $emailTempVariables['refundwalletorder'] = $this->getFormattedPriceAccToCurrency(
                $mailData['refundwalletorder'],
                2,
                $currency
            );
            $emailTempVariables['admindebit'] = $this->getFormattedPriceAccToCurrency(
                $mailData['admindebit'],
                2,
                $currency
            );
            $emailTempVariables['transfertocustomer'] = $this->getFormattedPriceAccToCurrency(
                $mailData['transfertocustomer'],
                2,
                $currency
            );
            $emailTempVariables['transfertobank'] = $this->getFormattedPriceAccToCurrency(
                $mailData['transfertobank'],
                2,
                $currency
            );
            $storeId = $this->getWebsiteStoreId($customer);
            if (!$storeId) {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $emailTempVariables['store'] = $this->storeManager->getStore($storeId);
            $emailTempVariables['store_id'] = $storeId;

            $adminEmail= $this->getDefaultTransEmailId();
            $adminUsername = 'Admin';
            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $this->tempId = $this->getMonthlystatementTemplateId();
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->addInfo($e->getMessage());
        }
    }

    /**
     * Sends mail for transaction
     *
     * @param int $customerId
     * @param array $params
     * @param object $store
     * @return void
     */
    public function sendMailForTransaction($customerId, $params, $store)
    {
        $walletTransaction = $this->walletTransaction->create();
        $type = $params['type'];
        $action = $params['action'];
        $sender = $this->customerModel
            ->create()
            ->load($customerId);
        $mailReciever = $this->customerModel
            ->create()
            ->load($customerId)->getData();

        if (array_key_exists('sender_id', $params)
            && $params['sender_id']>0
            && $params['type']==$walletTransaction::CUSTOMER_TRANSFER_TYPE
        ) {
            $sender = $this->customerModel
                ->create()
                ->load($params['sender_id']);
            $params['sender'] = $sender->getName();
            $params['email'] = $sender->getEmail();
        }
        $params['reciever'] = $mailReciever;
        $this->sendEmailToCustomer($sender, $params, $store, $type, $action);
        $this->sendEmailToAdmin($sender, $params, $store, $type, $action);
    }

    /**
     * Send email to admin
     *
     * @param object $customer
     * @param array $params
     * @param object $store
     * @param string $type
     * @param string $action
     * @return void
     */
    public function sendEmailToAdmin($customer, $params, $store, $type, $action)
    {
        $emailTemplateId = $this->getMailTemplateForTransactionForAdmin($type, $action);
        try {
            $emailTempVariables = $params;
            $emailTempVariables['customername'] = $customer->getName();
            $emailTempVariables['store'] = $store;
            $emailTempVariables['store_id'] = $store->getId();

            $adminEmail= $this->getDefaultTransEmailId();
            $adminUsername = 'Admin';
            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $this->tempId = $emailTemplateId;
            $this->inlineTranslation->suspend();
            $this->generateTemplate($emailTempVariables, $senderInfo, $receiverInfo);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Send email to csutomer
     *
     * @param object $customer
     * @param array $params
     * @param object $store
     * @param string $type
     * @param string $action
     * @return void
     */
    public function sendEmailToCustomer($customer, $params, $store, $type, $action)
    {
        $emailTemplateId = $this->getMailTemplateForTransactionForCustomer($type, $action);
        try {
            if (!isset($params['reciever_id'])) {
                $walletAmountReciever = $customer;
            } else {
                $walletAmountReciever = $reciever = $this->customerModel
                    ->create()
                    ->load($params['reciever_id'])->getData();
            }

            $walletTransaction = $this->walletTransaction->create();
            $transId = $walletTransaction->getCollection()
                ->addFieldToFilter('customer_id', $walletAmountReciever['entity_id'])
                ->setOrder('transaction_at', 'DESC')
                ->getFirstItem()
                ->getId();

            $walletAmountRecieverName = $walletAmountReciever['firstname'].' '.$walletAmountReciever['lastname'];
            $walletAmountRecieverEmail = $walletAmountReciever['email'];
            $emailTempVariables = $params;
            $emailTempVariables['recieveremail'] = $params['reciever']['email'];
            $emailTempVariables['recievername'] = $params['reciever']['firstname'].' '.$params['reciever']['lastname'];
            $emailTempVariables['customername'] = $emailTempVariables['recievername'];
            $emailTempVariables['store'] = $store;
            $emailTempVariables['transId'] = $transId;
            $emailTempVariables['walletAmountRecieverName'] = $walletAmountRecieverName;
            $emailTempVariables['walletAmountRecieverEmail'] = $walletAmountRecieverEmail;
            $emailTempVariables['email'] = $customer->getEmail();
            $emailTempVariables['store_id'] = $store->getId();

            $adminEmail= $this->getDefaultTransEmailId();
            $adminUsername = 'Admin';
            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $emailTempVariables['recievername'],
                'email' => $emailTempVariables['recieveremail'],
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $this->tempId = $emailTemplateId;
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Get mail template for transaction for customer
     *
     * @param string $type
     * @param string $action
     * @return void
     */
    public function getMailTemplateForTransactionForCustomer($type, $action)
    {
        $walletTransaction = $this->walletTransaction->create();
        if ($type == $walletTransaction::ORDER_PLACE_TYPE) {
            return $this->getTemplateOrderPlaceType($action, $walletTransaction);
        } elseif ($type == $walletTransaction::CASH_BACK_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletCashbackTemplateIdForCustomer();
            }
        } elseif ($type == $walletTransaction::REFUND_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletOrderRefundTemplateIdForCustomer();
            } else {
                return $this->getWalletAmountRefundTemplateIdForCustomer();
            }
        } elseif ($type == $walletTransaction::ADMIN_TRANSFER_TYPE) {
            return $this->getTemplateAdminTransferType($action, $walletTransaction);
        } elseif ($type == $walletTransaction::CUSTOMER_TRANSFER_TYPE) {
            return $this->getTemplateCustomerTransferType($action, $walletTransaction);
        } elseif ($type == $walletTransaction::CUSTOMER_TRANSFER_BANK_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                return $this->getCustomerBankTansferAmountTemplateIdForCustomer();
            }
        }
    }

    /**
     * Get mail template for transaction for admin
     *
     * @param string $type
     * @param string $action
     * @return mixed
     */
    public function getMailTemplateForTransactionForAdmin($type, $action)
    {
        $walletTransaction = $this->walletTransaction->create();
        if ($type == $walletTransaction::ORDER_PLACE_TYPE) {
            return $this->getAdminOrderPlace($action, $walletTransaction);
        } elseif ($type == $walletTransaction::CASH_BACK_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletCashbackTemplateIdForAdmin();
            }
        } elseif ($type == $walletTransaction::REFUND_TYPE) {
            return $this->getAdminRefundType($action, $walletTransaction);
        } elseif ($type == $walletTransaction::ADMIN_TRANSFER_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getAdminCreditAmountTemplateIdForAdmin();
            } else {
                return $this->getAdminDebitAmountTemplateIdForAdmin();
            }
        } elseif ($type == $walletTransaction::CUSTOMER_TRANSFER_TYPE) {
            return $this->getAdminCustomerTransferType($action, $walletTransaction);
        } elseif ($type == $walletTransaction::CUSTOMER_TRANSFER_BANK_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                return $this->getCustomerBankTansferAmountTemplateIdForAdmin();
            }
        }
    }

    /**
     * Get website store id
     *
     * @param object $customer
     * @param int $defaultStoreId
     * @return void
     */
    private function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Send mail to all approved transactions
     *
     * @param wallet transfer collection $collection
     */
    public function sendCustomerBulkTransferApproveMail($collection)
    {
        foreach ($collection->getData() as $customerToMailDetails) {
            $customerId = $customerToMailDetails['customer_id'];
            $customerData = $this->loadCustomer($customerId);

            $walletAmount = $customerToMailDetails['amount'];
            $bankDetails = $customerToMailDetails['bank_details'];
            $customerEmail = $customerData['email'];
            $customerName = $customerData['firstname'].' '.$customerData['lastname'];
            $finalData = [
                'name' => $customerName,
                'walletAmount' => $walletAmount,
                'bankDetails' => $bankDetails
            ];
            $this->sendAmountToBankMail($finalData, $customerEmail);
        }
    }

    /**
     * Send amount to bank mail
     *
     * @param array $finalData
     * @param string $customerEmail
     * @return void
     */
    public function sendAmountToBankMail($finalData, $customerEmail)
    {
        $emailTempVariables = $finalData;
        $emailTempVariables['store_id'] = $this->storeManager->getStore()->getId();
        $emailTempVariables['message'] = "Your request to transfer amount ".$finalData['walletAmount'];
        $emailTempVariables['message'] = $emailTempVariables['message']." in your bank account ";
        $emailTempVariables['message'].= $emailTempVariables['bankDetails']." has been approved";
        $adminEmail= $this->getDefaultTransEmailId();
        $adminUsername = 'Admin';
        $senderInfo = [];
        $receiverInfo = [];

        $receiverInfo = [
            'name' => $finalData['name'],
            'email' => $customerEmail,
        ];
        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $this->tempId = $this->getCustomerBulkTransferApproveMail();
        $this->inlineTranslation->suspend();
        try {
            $this->generateTemplate($emailTempVariables, $senderInfo, $receiverInfo);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Get Wallet Recharge/Used Template Id by action
     *
     * @param string $action
     * @param object $walletTransaction
     * @return mixed
     */
    public function getTemplateOrderPlaceType($action, $walletTransaction)
    {
        if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getWalletRechargeTemplateIdForCustomer();
        } else {
            return $this->getWalletUsedTemplateIdForCustomer();
        }
    }

    /**
     * Get Admin Debit/Credit Template Id by action
     *
     * @param string $action
     * @param object $walletTransaction
     * @return mixed
     */
    public function getTemplateAdminTransferType($action, $walletTransaction)
    {
        if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getAdminCreditAmountTemplateIdForCustomer();
        } else {
            return $this->getAdminDebitAmountTemplateIdForCustomer();
        }
    }

    /**
     * Get Customer Debit/Credit Template Id by action
     *
     * @param string $action
     * @param object $walletTransaction
     * @return mixed
     */
    public function getTemplateCustomerTransferType($action, $walletTransaction)
    {
        if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getCustomerCreditAmountTemplateIdForCustomer();
        } else {
            return $this->getCustomerDebitAmountTemplateIdForCustomer();
        }
    }

    /**
     * Get Wallet Used/Recharge Template Id by action
     *
     * @param string $action
     * @param object $walletTransaction
     * @return mixed
     */
    public function getAdminOrderPlace($action, $walletTransaction)
    {
        if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getWalletRechargeTemplateIdForAdmin();
        } else {
            return $this->getWalletUsedTemplateIdForAdmin();
        }
    }

    /**
     * Get Wallet Order/Recharge Refund Template Id by action
     *
     * @param string $action
     * @param object $walletTransaction
     * @return mixed
     */
    public function getAdminRefundType($action, $walletTransaction)
    {
        if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getWalletOrderRefundTemplateIdForAdmin();
        } else {
            return $this->getWalletAmountRefundTemplateIdForAdmin();
        }
    }

    /**
     * Get Customer transfer type Template by Transfer Type
     *
     * @param string $action
     * @param object $walletTransaction
     * @return mixed
     */
    public function getAdminCustomerTransferType($action, $walletTransaction)
    {
        if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            return $this->getCustomerCreditAmountTemplateIdForAdmin();
        } else {
            return $this->getCustomerDebitAmountTemplateIdForAdmin();
        }
    }

    /**
     * Load Customer By customer Id
     *
     * @param int $customerId
     * @return object \Magento\Customer\Model\Customer
     */
    public function loadCustomer($customerId)
    {
        return $this->customerModel->create()->load($customerId);
    }
}
