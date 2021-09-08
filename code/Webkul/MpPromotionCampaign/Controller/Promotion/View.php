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
namespace Webkul\MpPromotionCampaign\Controller\Promotion;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    /**
     * Result Page
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Campaign
     *
     * @var \Webkul\MpCampaign\Model\CampaignFactory
     */
    public $campaignFactory;

    /**
     * Helper
     *
     * @var \Webkul\MpCampaign\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MpCampaign\Model\CampaignFactory $campaignFactory
     * @param \Webkul\MpCampaign\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaignFactory,
        \Webkul\MpPromotionCampaign\Helper\Data $helper
    ) {
        $this->campaignFactory = $campaignFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Offer View
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $date = $this->helper->getCurrentDateTime();
        $currentDateTime = $this->helper->getDefaultZoneDateTime($date);
        $campaignId = $this->getRequest()->getParam('id');
        $collection = $this->campaignFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $campaignId)
                ->addFieldToFilter('start_date', ['lteq'=>$currentDateTime])
                ->addFieldToFilter('end_date', ['gteq'=>$currentDateTime]);
        if ($collection->getSize()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__($collection->getLastItem()->getTitle()));
            return $resultPage;
        }
        $this->messageManager->addError(__('The Promotion campaign does not exist or not live yet.'));
        return $this->resultRedirectFactory->create()->setPath(
            'mppromotioncampaign/promotion',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
