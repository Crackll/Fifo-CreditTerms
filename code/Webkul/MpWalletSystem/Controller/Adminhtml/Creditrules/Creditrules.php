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

namespace Webkul\MpWalletSystem\Controller\Adminhtml\Creditrules;

use Webkul\MpWalletSystem\Controller\Adminhtml\Creditrules as CreditrulesController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul MpWalletSystem Controller
 */
class Creditrules extends CreditrulesController
{
    /**
     * Controller Execute function
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpWalletSystem::walletcreditrules');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketplace Wallet System Credit Rules'));
        $resultPage->addBreadcrumb(
            __('Marketplace Wallet System Credit Rules'),
            __('Marketplace Wallet System Credit Rules')
        );
        return $resultPage;
    }
}
