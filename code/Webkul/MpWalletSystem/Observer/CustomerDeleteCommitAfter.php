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

namespace Webkul\MpWalletSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul MpWalletSystem Observer Class
 */
class CustomerDeleteCommitAfter implements ObserverInterface
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var $walletPayee
     */
    protected $walletPayee;

    /**
     * @var $walletTransaction
     */
    protected $walletTransaction;

    /**
     * @var $walletRecord
     */
    protected $walletRecord;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Webkul\MpWalletSystem\Helper\Data          $helper
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPayment,
        \Webkul\MpWalletSystem\Model\WalletPayeeFactory $walletPayee,
        \Webkul\MpWalletSystem\Model\WallettransactionFactory $walletTransaction,
        \Webkul\MpWalletSystem\Model\WalletrecordFactory $walletRecord
    ) {
        
        $this->splitPayment = $splitPayment;
        $this->helper = $helper;
        $this->walletPayee = $walletPayee;
        $this->walletTransaction = $walletTransaction;
        $this->walletRecord = $walletRecord;
    }

    /**
     * Customer delete event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();
        $this->deletePayeeFromList($customerId);
        $this->deleteTransactionForCustomer($customerId);
        $this->deleteRecordForCustomer($customerId);
        return $this;
    }

    /**
     * Delete payee from list
     *
     * @param int $customerId
     * @return void
     */
    protected function deletePayeeFromList($customerId)
    {
        $walletPayeeCollection = $this->walletPayee->create()->getCollection()
            ->addFieldToFilter('payee_customer_id', $customerId);
        if ($walletPayeeCollection->getSize()) {
            foreach ($walletPayeeCollection as $payee) {
                $this->splitPayment->deleteMethod($payee);
            }
        }
    }

    /**
     * Delete transaction for customer
     *
     * @param int $customerId
     * @return void
     */
    protected function deleteTransactionForCustomer($customerId)
    {
        $walletTransactionCollection = $this->walletTransaction->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        if ($walletTransactionCollection->getSize()) {
            foreach ($walletTransactionCollection as $walletTransactionData) {
                $this->splitPayment->deleteMethod($walletTransactionData);
            }
        }
    }

    /**
     * Delete record for customer
     *
     * @param int $customerId
     * @return void
     */
    protected function deleteRecordForCustomer($customerId)
    {
        $walletRecordCollection = $this->walletRecord->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        if ($walletRecordCollection->getSize()) {
            foreach ($walletRecordCollection as $walletRecordData) {
                $this->splitPayment->deleteMethod($walletRecordData);
            }
        }
    }
}
