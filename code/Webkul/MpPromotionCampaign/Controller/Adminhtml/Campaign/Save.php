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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Save extends Action
{
    /**
     * Core Registry
     *
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;

    /**
     * Campaign Factory
     *
     * @var \Webkul\MpPromotionCampaign\Model\CampaignFactory
     */
    public $campaignFactory;
    
    /**
     * File
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $_fileFactory;

    /**
     * DateTime
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     */
    public $_dateFilter;

    /**
     * File Upload
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    public $_fileUploaderFactory;

    /**
     * Logger
     *
     * @var \Webkul\MpPromotionCampaign\Logger\Logger
     */
    public $logger;

    /**
     * Helper
     *
     * @var \Webkul\MpPromotionCampaign\Helper\Data
     */
    public $helper;

    /**
     * Directory
     */
    public $_mediaDirectory;

    /**
     * @var \Webkul\MpPromotionCampaign\Controller\Adminhtml\Campaign\Banner\Upload
     */
    protected $virtualImageUploader;

    /**
     * Construct
     *
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\DateTime $dateFilter
     * @param \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaignFactory
     * @param \Webkul\MpPromotionCampaign\Logger\Logger $logger
     * @param \Webkul\MpPromotionCampaign\Helper\Data $helper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Webkul\MpPromotionCampaign\Controller\Adminhtml\Campaign\Banner\Upload $virtualImageUploader
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\DateTime $dateFilter,
        \Webkul\MpPromotionCampaign\Model\CampaignFactory $campaignFactory,
        \Webkul\MpPromotionCampaign\Logger\Logger $logger,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        Filesystem $filesystem,
        \Webkul\MpPromotionCampaign\Controller\Adminhtml\Campaign\Banner\Upload $virtualImageUploader
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_dateFilter = $dateFilter;
        $this->campaignFactory = $campaignFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->virtualImageUploader = $virtualImageUploader;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
      
        if ($this->getRequest()->getPostValue()) {
            try {
                $campaign = $this->campaignFactory->create();
                $redirectBack = $this->getRequest()->getParam('back', false);
                $data = $this->getRequest()->getPostValue();
                $generalData = $this->getRequest()->getParam('general');
                $bannerData = $this->getRequest()->getParam('promotion');
                $descriptionData = $this->getRequest()->getParam('terms');

                $id = '';
                if (!empty($generalData['entity_id'])) {
                    $id = $generalData['entity_id'];
                    $campaign = $this->campaignFactory->create()->load($id);
                } else {
                    $campaign = $this->campaignFactory->create();
                }
                if (!$campaign->getId() && $id) {
                    $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                // filter post data
                $id = $this->getRequest()->getParam('entity_id');
                $campaign = $this->campaignFactory->create();
                $descriptionData['description'] = $this->filterData($descriptionData['description']);
                $generalData['title'] = $this->filterData($generalData['title']);
                $generalData['discount'] = min(100, $generalData['discount']);
                $generalData['status'] =  $generalData['status'];
                $filterValues = ['start_date' => $this->_dateFilter];
                if ($this->getRequest()->getParam('end_date')) {
                    $filterValues['end_date'] = $this->_dateFilter;
                }
                $inputFilter = new \Zend_Filter_Input(
                    $filterValues,
                    [],
                    $generalData
                );
                $generalData = $inputFilter->getUnescaped();

                $descriptionInputFilter = new \Zend_Filter_Input(
                    $filterValues,
                    [],
                    $descriptionData
                );
                $descriptionData = $descriptionInputFilter->getUnescaped();
                if (isset($generalData['entity_id'])) {
                    if ($generalData['entity_id']=="" && $generalData['entity_id']==0) {
                        unset($generalData['entity_id']);
                    }
                }

                // Save uploaded banner to respective folder
                if (!empty($bannerData['banner'])) {
                    $bannerArr = $bannerData['banner'];
                    if (( !empty($bannerArr[0]['name'])) && !empty($bannerArr[0]['tmp_name'])) {
                        $bannerData['banner'] = $bannerData['banner'][0]['name'];
                        $this->imageUploader = $this->virtualImageUploader->returnImageUploader();
                        $this->imageUploader->moveFileFromTmp($bannerData['banner']);
                        $bannerData['banner'] = '/mppromotioncampaign/'.$bannerData['banner'];
                    } elseif (!empty($bannerData['banner'][0]['url'])) {
                        $bannerUrlArr = explode('pub/media', $bannerData['banner'][0]['url']);
                        if (!empty($bannerUrlArr[1])) {
                            $bannerData['banner'] = $bannerUrlArr[1];
                        } else {
                            $bannerData['banner'] = '/mppromotioncampaign/'.$bannerData['banner'][0]['name'];
                        }
                    } elseif (!empty($bannerData['banner'][0]['name'])) {
                        $bannerData['banner'] = '/mppromotioncampaign/'.$bannerData['banner'][0]['name'];
                    }
                } else {
                    $bannerData['banner'] = null;
                }
                $campaign->addData($generalData);
                $campaign->addData($descriptionData);
                $campaign->addData($bannerData);
                $campaign->save();

                // Save Campaign products dates
                $this->helper->updateCampaignProductsDates($generalData);

                if ($id) {
                    $this->messageManager->addSuccess(__('You updated the campaign.'));
                } else {
                    $this->messageManager->addSuccess(__('You saved the campaign.'));
                }
                if ($redirectBack) {
                    $this->_redirect('mppromotioncampaign/*/edit', ['id' => $campaign->getId()]);
                    return;
                }
                $this->_redirect('mppromotioncampaign/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->info($e->getMessage());
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('entity_id');
                if (!empty($id)) {
                    $this->_redirect('mppromotioncampaign/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('mppromotioncampaign/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
                $this->messageManager->addError(
                    __('Something went wrong while saving the campaign data. Please review the error log.')
                );
                $this->_redirect('mppromotioncampaign/*/edit', ['id' => $this->getRequest()->getParam('entity_id')]);
                return;
            }
        }
        return $this->_redirect('mppromotioncampaign/*/edit', ['id' => $this->getRequest()->getParam('entity_id')]);
    }
    
    /**
     * Filter Data
     *
     * @param string $data
     * @return string
     */
    private function filterData($data)
    {
        $data = str_replace("<script>", "&lt;script&gt;", $data);
        $data = str_replace("</script>", "&lt;/script&gt;", $data);
        return $data;
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
}
