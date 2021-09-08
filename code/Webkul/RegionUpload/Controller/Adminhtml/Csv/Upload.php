<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RegionUpload\Controller\Adminhtml\Csv;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Webkul\RegionUpload\Helper\Data;
use Webkul\RegionUpload\Model\SaveRegion;

class Upload extends Action
{

    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Undocumented function
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param SaveRegion $saveRegion
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        SaveRegion $saveRegion
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->saveRegion = $saveRegion;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_RegionUpload::region_upload");
    }

    public function execute()
    {
        $param = $this->_request->getParams();
        $countryId = $param['wk_country_id'];
        $file = $this->getRequest()->getFiles();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!empty($file)) {
            $validateData = $this->helper->validateUploadedFiles();
            if ($validateData['error']) {
                $this->messageManager->addError(__($validateData['msg']));
                return $resultRedirect->setPath('regionupload/view/index');
            }
            $csvData = $this->helper->getCsvData($file);
            foreach ($csvData as $key => $value) {
                if ($csvData[0][0]=='region_code' && $csvData[0][1]=='region_name') {
                    if ($key != 0) {
                        $saveData = $this->saveRegion->saveRegionCodeCsv($value, $countryId);
                        if ($saveData['error']) {
                            $this->messageManager->addError(__('Invalid Response'));
                            return $resultRedirect->setPath('regionupload/view/index');
                        }
                    }
                } else {
                    $this->messageManager->addError(__('Invalid Response'));
                    return $resultRedirect->setPath('regionupload/view/index');
                }
            }
            $this->messageManager->addSuccess(
                __(
                    'Region Code Successfully Added.'
                )
            );
        }
        return $resultRedirect->setPath('regionupload/view/index');
    }
}
