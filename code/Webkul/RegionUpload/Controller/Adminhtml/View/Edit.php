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
namespace Webkul\RegionUpload\Controller\Adminhtml\View;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Controller\ResultFactory;
use Webkul\RegionUpload\Model\SaveRegion;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    protected $resultPageFactory;

    /**
     * Undocumented function
     *
     * @param Context $context
     * @param RegionFactory $regionFactory
     * @param SaveRegion $saveRegion
     */
    public function __construct(
        Context $context,
        RegionFactory $regionFactory,
        SaveRegion $saveRegion,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->regionFactory = $regionFactory;
        $this->saveRegion = $saveRegion;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    // protected function _isAllowed()
    // {
    //     return $this->_authorization->isAllowed("Webkul_RegionUpload::region_upload");
    // }

    public function execute()
    {
        $param = $this->_request->getParams();
        
        // if (!empty($param)) {
        //     $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //     return $resultRedirect->setPath('regionupload/view/index');
        // }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_RegionUpload::region_upload');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Region'));
        return $resultPage;
    }
}
