<?php
namespace WeltPixel\Backend\Controller\Adminhtml\Licenses;

class Post extends \WeltPixel\Backend\Controller\Adminhtml\Licenses
{
    const ADMIN_RESOURCE = 'WeltPixel_Backend::Modules_License';

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = [];

        $lcK = trim($this->getRequest()->getParam('license'));
        $moduleName = $this->getRequest()->getParam('module_name');

        try {
            $license = $this->licenseFactory->create();
            $license->load($moduleName, 'module_name');

            $license->setModuleName($moduleName);
            $license->setLicenseKey($lcK);
            $license->save();
            $license->updMdsInf();

            $result['error'] = false;
            $result['license'] = $lcK;
            $result['module_name'] = $moduleName;
            $licenseValidity = $license->isLcVd($lcK, $moduleName);
            $result['is_valid'] = ($licenseValidity) ? 'license-status-ok' : 'license-status-nok';
            $result['is_valid_msg'] = ($licenseValidity) ? __('Valid') : __('Invalid');
            $result['message'] = __('The license key was successfully updated.');
            $result['message'] .= "<br/><br/>";
            if (!$licenseValidity) {
                $result['message'] .= '<div class="license-pmsg valid-license-pmsg">' . __('The license key introduced is valid') . '</div>';
            } 
        } catch (\Exception $ex) {
            $result['error'] = true;
            $result['message'] = __('There was a problem with the license key saving.') . ' ' . $ex->getMessage();
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }
}