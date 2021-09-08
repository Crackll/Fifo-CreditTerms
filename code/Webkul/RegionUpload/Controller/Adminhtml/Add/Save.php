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
namespace Webkul\RegionUpload\Controller\Adminhtml\Add;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Controller\ResultFactory;
use Webkul\RegionUpload\Model\SaveRegion;

class Save extends Action
{
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
        SaveRegion $saveRegion
    ) {
        parent::__construct($context);
        $this->regionFactory = $regionFactory;
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
        
        if (!empty($param)) {
            $result = $this->saveRegion->saveRegion($param);
            if ($result['error']) {
                $this->messageManager->addError($result['message']);
            } else {
                $this->messageManager->addSuccess($result['message']);
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('regionupload/view/index');
    }
}
