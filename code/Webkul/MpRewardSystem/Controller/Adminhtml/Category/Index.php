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

namespace Webkul\MpRewardSystem\Controller\Adminhtml\Category;

use Webkul\MpRewardSystem\Controller\Adminhtml\Category as CategoryController;
use Magento\Framework\Controller\ResultFactory;

class Index extends CategoryController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpRewardSystem::menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Reward Points on Category'));
        $resultPage->addBreadcrumb(__('Reward Points on Category'), __('Reward Points on Category'));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \Webkul\MpRewardSystem\Block\Adminhtml\Category\Edit::class
            )
        );
        $resultPage->addLeft(
            $resultPage->getLayout()->createBlock(
                \Webkul\MpRewardSystem\Block\Adminhtml\Category\Edit\Tabs::class
            )
        );
        return $resultPage;
    }
}
