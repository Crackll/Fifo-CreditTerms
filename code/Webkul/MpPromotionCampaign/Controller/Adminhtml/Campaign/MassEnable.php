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

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;
use Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\CollectionFactory;

class MassEnable extends \Magento\Backend\App\Action
{
    /**
     * Filter.
     *
     * @var Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\CollectionFactory
     */
    public $collectionFactory;

    /**
     * Logger
     *
     * @var \Webkul\MpPromotionCampaign\Logger\Logger
     */
    public $logger;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\CollectionFactory $collectionFactory
     * @param \Webkul\MpPromotionCampaign\Logger\Logger $logger
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Webkul\MpPromotionCampaign\Logger\Logger $logger,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->helper = $helper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $count = 0;
            foreach ($collection as $campaign) {
                $campaign->setStatus(CampaignModel::STATUS_ENABLED);
                $this->saveObject($campaign);
                $count++;
            }
            $this->helper->cacheFlush();
            $this->messageManager->addSuccess(__('A total of %1 campaign(s) have been enabled.', $count));
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index/');
    }

    /**
     * check permission
     *
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpPromotionCampaign::campaign');
    }

    /**
     * saveObject
     * @param Object $object
     * @return void
     */
    public function saveObject($object)
    {
        $object->save();
    }
}
