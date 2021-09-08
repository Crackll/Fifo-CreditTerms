<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_CustomerSubaccount
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer class.
 */
class MultishippingCheckoutPredispatch implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseFactory
     */
    public $responseFactory;

    /**
     * URL
     *
     * @var \Magento\Framework\UrlInterface
     */
    public $url;

    /**
     * Message Manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Constructor
     *
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement
    ) {
        $this->helper = $helper;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->messageManager = $messageManager;
        $this->accountManagement = $accountManagement;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->canPlaceOrder() ||
            (
                $this->helper->canPlaceOrder()
                && $this->helper->isCartApprovalRequired()
                && !$this->helper->isCartApproved()
            )
        ) {
            $this->messageManager->addWarning(__("You are not authorised to place this order."));
            $redirectionUrl = $this->url->getUrl('checkout/cart');
            $controller = $observer->getControllerAction();
            $controller->getResponse()->setRedirect($redirectionUrl);
        }
        if ($this->helper->isForcedMainAddress()) {
            $mainAccId = $this->helper->getMainAccountId();
            $noaddress = false;
            try {
                $shipAddress = $this->accountManagement->getDefaultShippingAddress($mainAccId);
                if (!$shipAddress) {
                    $noaddress = true;
                }
            } catch (\Exception $e) {
                $noaddress = true;
            }
            if ($noaddress) {
                $this->messageManager->addWarning(__("Please ask Main Account user to add shipping address or remove Force Usage Main Account Address."));
                $redirectionUrl = $this->url->getUrl('checkout/cart');
                $controller = $observer->getControllerAction();
                $controller->getResponse()->setRedirect($redirectionUrl);
            }
        }
        return $this;
    }
}
