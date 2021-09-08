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

namespace Webkul\MpRewardSystem\Controller\Adminhtml\Product;

use Webkul\MpRewardSystem\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class Index extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpRewardSystem::product');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Reward Points on Product'));
        $resultPage->addBreadcrumb(__('Manage Reward Points on Product'), __('Manage Reward Points on Product'));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \Webkul\MpRewardSystem\Block\Adminhtml\Product\Edit::class
            )
        );
        $resultPage->addLeft(
            $resultPage->getLayout()->createBlock(
                \Webkul\MpRewardSystem\Block\Adminhtml\Product\Edit\Tabs::class
            )
        );
        return $resultPage;
    }
}
