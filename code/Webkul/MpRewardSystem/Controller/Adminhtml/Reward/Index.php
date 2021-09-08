<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Controller\Adminhtml\Reward;

use Webkul\MpRewardSystem\Controller\Adminhtml\Reward as RewardController;
use Magento\Framework\Controller\ResultFactory;

class Index extends RewardController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpRewardSystem::rewardsystem');
        $resultPage->getConfig()->getTitle()->prepend(__('Reward System Details'));
        $resultPage->addBreadcrumb(__('Reward System Details'), __('Reward System Details'));
        return $resultPage;
    }
}
