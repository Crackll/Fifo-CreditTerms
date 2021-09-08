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

namespace Webkul\MpWalletSystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Webkul MpWalletSystem Controller
 */
class View extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var WallettransactionFactory
     */
    protected $walletTransaction;
    
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Initialize dependencies
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WallettransactionFactory $walletTransaction,
        CustomerSession $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletTransaction = $walletTransaction;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
    
    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (is_array($params) && array_key_exists('entity_id', $params)) {
            $walletTransaction = $this->walletTransaction->create()->load($params['entity_id']);
            $customerId = $this->customerSession->getCustomerId();
            if ($walletTransaction && $walletTransaction->getEntityId()
                && ($customerId==$walletTransaction->getCustomerId())
            ) {
                /**
                 * @var \Magento\Framework\View\Result\Page $resultPage
                 */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(
                    __('View Transaction')
                );
                return $resultPage;
            } else {
                $this->messageManager->addError(__("You are not authorized to access this transaction!"));
                return $this->resultRedirectFactory->create()->setPath(
                    'mpwalletsystem/index/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        }
    }
}
