<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerBuyerCommunication
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerBuyerCommunication\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication;
use Magento\Framework\Message\ManagerInterface;

class AdminhtmlCustomerSaveAfterObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var SellerBuyerCommunication
     */
    protected $sellerBuyerCommunication;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        SellerBuyerCommunication $sellerBuyerCommunication,
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->sellerBuyerCommunication = $sellerBuyerCommunication;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $request_data = $this->request->getPost();
            $customer_id = $observer->getCustomer()->getId();
            $sellerData = $this->sellerBuyerCommunication->getCollection();
            $sellerData->addFieldToFilter('email_id', ['eq'=>$request_data['customer']['email']]);
            if ($sellerData->getSize()) {
                foreach ($sellerData as $seller) {
                    $seller->setCustomerId($customer_id);
                    $seller->setCustomerId($observer->getCustomer()->getFirstName()
                    ." ".$observer->getCustomer()->getLastName());
                    $seller->save();
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }
}
