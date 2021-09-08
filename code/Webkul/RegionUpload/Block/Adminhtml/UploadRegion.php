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
namespace Webkul\RegionUpload\Block\Adminhtml;

class UploadRegion extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Prepare button.
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProp = [
            'id' => 'wk_submit',
            'label' => __('Submit'),
            'class' => 'action-primary',
        ];
        $this->buttonList->add('wk_submit_region', $addButtonProp);
        if (!$this->getRequest()->getParam('region_id')) {
            $addButtonProps = [
                'id' => 'wk_upload_csv',
                'label' => __('Upload CSV'),
                'class' => 'action-secondary',
            ];
            $this->buttonList->add('add_new', $addButtonProps);
        }
        
        return parent::_prepareLayout();
    }
}
