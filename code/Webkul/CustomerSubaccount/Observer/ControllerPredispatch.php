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
class ControllerPredispatch implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Subaccount Cart
     *
     * @var \Webkul\CustomerSubaccount\Model\CartFactory
     */
    public $subaccCartFactory;

    /**
     * Email Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Email
     */
    public $emailHelper;

    /**
     * Redirect
     *
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    public $redirect;

    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * Constructor
     *
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     * @param \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory
     * @param \Webkul\CustomerSubaccount\Helper\Email $emailHelper
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Webkul\CustomerSubaccount\Model\SubaccountFactory $subaccountFactory,
        \Webkul\CustomerSubaccount\Helper\Email $emailHelper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
        $this->subaccountFactory = $subaccountFactory;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerId = $this->helper->getCustomerId();
        if ($customerId
            && $this->helper->isSubaccountUser($customerId)
        ) {
            $route = $this->request->getFrontName();
            $controller = $this->request->getControllerName();
            $action = $this->request->getActionName();
            $currentArray = [$route, $route.'_'.$controller, $route.'_'.$controller.'_'.$action];
            $subAccount = $this->subaccountFactory->create()->load($customerId, 'customer_id');
            $forbiddenArrray = explode(',', $subAccount->getForbiddenAccess());
            foreach ($forbiddenArrray as &$forbidden) {
                $forbidden = trim($forbidden);
            }
            $currentArray = array_diff($currentArray, ['','_','__']);
            if (!empty(array_intersect($currentArray, $forbiddenArrray))) {
                $this->messageManager->addError(__('Access Forbidden'));
                $controller = $observer->getControllerAction();
                $controller->getResponse()->setRedirect($this->redirect->getRefererUrl());
            }
        }
    }
}
