<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */

namespace Webkul\WebApplicationFirewall\Block\Adminhtml\FileSystemPermission;

class PermissionViewer extends \Magento\Backend\Block\Template
{
    /**
     * @var $fileSystemPermissionHelper
     */
    private $fileSystemPermissionHelper;

    /**
     * Constructor
     * @param \Magento\Backend\Block\Template\Context                          $context
     * @param \Webkul\WebApplicationFirewall\Helper\FileSystemPermissionHelper $fileSystemPermissionHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\WebApplicationFirewall\Helper\FileSystemPermissionHelper $fileSystemPermissionHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->fileSystemPermissionHelper = $fileSystemPermissionHelper;
    }

    /**
     * get Magento Mode
     * @param void
     * @return string
     */
    public function getMagentoMode()
    {
        return $this->fileSystemPermissionHelper->getMagentoMode();
    }
}
