<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Controller\Adminhtml\Cart;

use Webkul\MpRewardSystem\Controller\Adminhtml\Cart as CartController;
use Magento\Framework\Controller\ResultFactory;

class Index extends CartController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpRewardSystem::menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Reward Points on Cart'));
        $resultPage->addBreadcrumb(__('Manage Reward Points on Cart'), __('Manage Reward Points on Cart'));
        return $resultPage;
    }
}
