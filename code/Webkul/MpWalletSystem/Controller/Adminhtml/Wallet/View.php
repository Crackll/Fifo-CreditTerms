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

use Magento\Backend\App\Action;
use Webkul\MpWalletSystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MpWalletSystem\Model\WallettransactionFactory;

/**
 * Webkul MpWalletSystem Controller
 */
class View extends WalletController
{

    protected $walletTransaction;

    /**
     * Initialize dependencies
     *
     * @param ActionContext                          $context
     * @param WallettransactionFactory               $transactionFactory
     */
    public function __construct(
        Action\Context $context,
        WallettransactionFactory $transactionFactory
    ) {
        $this->walletTransaction = $transactionFactory;
        parent::__construct($context);
    }
    
    /**
     * Controller Execute function
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        if (is_array($params) && array_key_exists('entity_id', $params) && $params['entity_id']!='') {
            $walletTransactionModel = $this->walletTransaction->create()
                ->load($params['entity_id']);
            if (!empty($walletTransactionModel) && $walletTransactionModel->getEntityId()) {
                $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $resultPage->setActiveMenu('Webkul_MpWalletSystem::walletsystem');
                $resultPage->getConfig()->getTitle()->prepend(__('Marketplace Wallet System Transaction View'));
                $resultPage->addBreadcrumb(
                    __('Marketplace Wallet System Transaction View'),
                    __('Marketplace Wallet System Transaction View')
                );
                return $resultPage;
            }
        }
        $this->messageManager->addError(
            __('Transaction not exists.')
        );
        return $resultRedirect->setPath('mpwalletsystem/wallet/index');
    }
}
