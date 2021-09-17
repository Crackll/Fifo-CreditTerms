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
use Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory;
use Webkul\MpPromotionCampaign\Model\CampaignProductFactory;
use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;
use Webkul\MpPromotionCampaign\Model\CampaignProduct as CampaignProModel;

class MassRefuse extends \Magento\Backend\App\Action
{
    /**
     * Filter.
     *
     * @var Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory
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
     * @param \Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory $collectionFactory
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Webkul\MpCampaign\Logger\Logger $logger
     */
    public function __construct(
        CampaignProductFactory $campaignProduct,
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Webkul\MpPromotionCampaign\Logger\Logger $logger,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->campaignProduct = $campaignProduct;
        $this->redirect = $redirect;
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
            $url = $this->redirect->getRefererUrl();
            $urlData = $this->helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
            $count = 0;
            $selectedId = [];
            $selectedId = $this->getRequest()->getparam('selected');
            $campaignsts = $this->helper->campaignStatus($campaignId);
            $campaignstatus = $campaignsts['code'];
            if ($campaignstatus == CampaignModel::CAMPAIGN_STATUS_COMMINGSOON) {
                if (isset($selectedId)) {
                    foreach ($selectedId as $id) {
                        $this->campaignProduct->create()
                            ->load($id)
                            ->setStatus(CampaignProModel::STATUS_REFUSE)
                            ->save();
                        $count++;
                    }
                } else {
                    $campaignProduct = $this->collectionFactory->create()
                                    ->addFieldToFilter('campaign_id', $campaignId);
                    foreach ($campaignProduct as $data) {
                        $data->setStatus(CampaignProModel::STATUS_REFUSE);
                        $data->save();
                        $count++;
                    }
                }
                $this->helper->cacheFlush();
                $this->messageManager->addSuccess(
                    __('A total of %1 product(s) have been refused to join campaign.', $count)
                );
            } elseif ($campaignstatus == 1) {
                $this->messageManager->addError(
                    __('The Campaign is Live, You can not refuse the products.')
                );
            }
            
        } catch (\Exception $e) {
            $this->logger->info(
                "MassRefuse::excute ".$e->getMessage()
            );
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        )->setPath('*/*/product/id/'.$campaignId);
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
     * delete
     * @param Object $object
     * @return void
     */
    public function deleteObject($object)
    {
        $object->delete();
    }
}
