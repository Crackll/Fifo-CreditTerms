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
use Webkul\MpWalletSystem\Model\Walletrecord;

/**
 * Webkul MpWalletSystem Observer Class
 */
class SalesOrderPayInvoice implements ObserverInterface
{
    /**
     * @var \Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\Walletrecord
     */
    protected $walletrecordModel;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\MpWalletSystem\Helper\Data          $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Walletrecord                                $walletRecord
     * @param Magento\Framework\App\Request\Http          $request
     */
    public function __construct(
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Walletrecord $walletRecord,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->date = $date;
        $this->walletrecordModel = $walletRecord;
        $this->request = $request;
    }

    /**
     * Invoice save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $invoice = $event->getInvoice();
        $order = $invoice->getOrder();
        $orderTotalPaid = $order->getTotalPaid();
        $orderBaseTotalPaid = $order->getBaseTotalPaid();
        $walletAmount = (-1) * ($invoice->getWalletAmount());
        $baseWalletAmount = (-1) * ($this->helper->baseCurrencyAmount($invoice->getWalletAmount()));
        return $this;
    }
}
