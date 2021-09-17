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

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class SellerJoin extends \Magento\Customer\Controller\AbstractAccount implements CsrfAwareActionInterface
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
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpPromotionCampaign\Model\CampaignJoinFactory $campaignJoin
    ) {
        $this->campaignJoin = $campaignJoin;
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        parent::__construct($context);
    }
    
    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = [];
        if ($this->marketplaceHelper->isSeller()) {
            $data['campaign_id'] = $this->getRequest()->getPost('campaignId');
            $data['seller_id']   =  $this->marketplaceHelper->getCustomerId();
            $sellerCampaignData = $this->campaignJoin->create()
                ->getCollection()
                ->addFieldToFilter('campaign_id', $data['campaign_id'])
                ->addFieldToFilter('seller_id', $data['seller_id']);
            if (!$sellerCampaignData->getSize()) {
                $joinCampaign = $this->campaignJoin->create();
                $joinCampaign->setData($data)->save();
                if ($joinCampaign->getId()) {
                    $this->messageManager->addSuccess(__('You successfully joined this campaign.'));
                }
            }
            return $this->resultRedirectFactory->create()->setPath(
                'mppromotioncampaign/campaign/join/id/'.$data['campaign_id'],
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
