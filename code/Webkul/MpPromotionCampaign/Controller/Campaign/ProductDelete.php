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
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpPromotionCampaign\Model\ResourceModel\CampaignProduct\CollectionFactory;
use Webkul\MpPromotionCampaign\Model\Campaign as CampaignModel;

class ProductDelete extends \Magento\Customer\Controller\AbstractAccount implements CsrfAwareActionInterface
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
     * Rule
     *
     * @var \Magento\SalesRule\Model\Rule
     */
    public $ruleFactory;

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
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        CollectionFactory $collectionFactory,
        \Webkul\MpPromotionCampaign\Logger\Logger $logger,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct
    ) {
        $this->campaignProduct = $campaignProduct;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->redirect = $redirect;
        $this->helper = $helper;
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
        try {
            $url = $this->redirect->getRefererUrl();
            $urlData = $this->helper->getCampaignIdFromUrl($url);
            $campaignId = $urlData;
            $count = 0;
            $campaignStatus = $this->helper->campaignStatus($campaignId);
            if ($campaignStatus['code'] == CampaignModel::CAMPAIGN_STATUS_COMMINGSOON) {
                $selectedRows = $this->getRequest()->getParam('selected');
                if (empty($selectedRows) && null !== $this->getRequest()->getParam('excluded')) {
                    $collection = $this->collectionFactory->create();
                    foreach ($collection as $campaign) {
                        $id = $campaign->getId();
                        $this->campaignProduct->create()
                            ->getCollection()
                            ->addFieldToFilter('campaign_id', $id)
                            ->walk('delete');
                        $this->deleteObject($campaign);
                        $count++;
                    }
                    $this->helper->cacheFlush();
                    $this->messageManager->addSuccess(__('A total of %1 product(s) have been deleted.', $count));
                } else {
                    foreach ($selectedRows as $select) {
                        $this->campaignProduct->create()->load($select)->delete();
                        $count++;
                    }
                    $this->helper->cacheFlush();
                    $this->messageManager->addSuccess(__('A total of %1 product(s) have been deleted.', $count));
                }
            } else {
                $this->messageManager->addError(__("Campaign already start. You can't update product"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($url);
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
