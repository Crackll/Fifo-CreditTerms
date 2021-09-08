<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPromotionCampaign\Controller\Campaign;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Join extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Result Page
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Marketplace Helper
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $marketplaceHelper;

    /**
     * @var \Webkul\MpPromotionCampaign\Model\CampaignFactory
     */
    private $campaign;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaign
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaign
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->campaign = $campaign;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->marketplaceHelper->isSeller()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id) {
                $campaign = $this->campaign->create();
                $campaign->load($id);

                if (!$campaign->getEntityId()) {
                    $this->messageManager->addError(__('This campaign no longer exists.'));
                    $this->_redirect('*/*/index');
                    return;
                }
            }
            $resultPage = $this->resultPageFactory->create();
            if ($this->marketplaceHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mppromotioncampaign_campaign_join_layout2');
            }
            if ($campaign->getTitle()) {
                $resultPage->getConfig()->getTitle()->prepend(__($campaign->getTitle()));
            } else {
                $resultPage->getConfig()->getTitle()->prepend(__('Promotion'));
            }
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
