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
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul MpWalletSystem Controller
 */
class Index extends WalletController
{
    /**
     * Controller Execute function
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpWalletSystem::walletsystem');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketplace Wallet System Details'));
        $resultPage->addBreadcrumb(__('Marketplace Wallet System Details'), __('Marketplace Wallet System Details'));
        return $resultPage;
    }
}
