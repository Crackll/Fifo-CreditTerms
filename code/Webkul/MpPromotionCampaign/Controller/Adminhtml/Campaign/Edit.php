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

namespace Webkul\MpPromotionCampaign\Controller\Adminhtml\Campaign;

use Magento\Framework\Locale\Resolver;
use Webkul\MpPromotionCampaign\Model\CampaignFactory;
use Magento\Framework\Registry;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\MpPromotionCampaign\Model\CampaignFactory
     */
    private $campaign;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Rule
     *
     * @var \Magento\SalesRule\Model\Rule
     */
    public $ruleFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param CampaignFactory $campaign
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CampaignFactory $campaign,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->campaign = $campaign;
        $this->ruleFactory = $ruleFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $campaign = $this->campaign->create();
        if ($id) {
            $campaign->load($id);

            if (!$campaign->getEntityId()) {
                $this->messageManager->addError(__('This campaign no longer exists.'));
                $this->_redirect('*/*/index');
                return;
            }
        }
        
        $this->coreRegistry->register('campaign', $campaign);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpPromotionCampaign::campaign');
        if ($campaign->getTitle()) {
            $resultPage->getConfig()->getTitle()->prepend(__($campaign->getTitle()));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Manage Promotion Campaign'));
        }
        
        return $resultPage;
    }

    /**
     * check permission
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpPromotionCampaign::campaign');
    }
}
