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

namespace Webkul\WebApplicationFirewall\Controller\Adminhtml\FileSystemPermission;

class DirectoryContentProvider extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Webkul_WebApplicationFirewall::permissionviewer';

    /**
     * @var $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var $jsonHelper
     */
    protected $jsonHelper;

    /**
     * @var $fileSystemPermissionHelper
     */
    protected $fileSystemPermissionHelper;

    /**
     * Constructor
     * @param Magento\Backend\App\Action\Context          $context
     * @param Magento\Framework\View\Result\PageFactory   $resultPageFactory
     * @param Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param Magento\Framework\Filesystem\Driver\File    $file
     * @param Psr\Log\LoggerInterface                     $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Webkul\WebApplicationFirewall\Helper\FileSystemPermissionHelper $fileSystemPermissionHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->fileSystemPermissionHelper = $fileSystemPermissionHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = [];
        try {
            $path = $this->getRequest()->getParam('path');
            $serverType = $this->getRequest()->getParam('serverType');

            if ($serverType == 'shared' || $serverType == 'private') {
                $response['data'] = $this->fileSystemPermissionHelper->getReadDirectoryWithInfo($path, $serverType);
                if ($response['data']) {
                    $parentDirectoryData = $this->fileSystemPermissionHelper->getParentDirectory($path, $serverType);
                    if ($parentDirectoryData) {
                        array_unshift($response['data'], $parentDirectoryData);
                    }

                    $response['data'] = $this->fileSystemPermissionHelper->getSerialize($response['data']);
                    $response['status'] = 'success';
                } else {
                    $response['status'] = 'failed';
                }
            } else {
                $response['status'] = 'failed';
            }
        } catch (\Exception $ex) {
            $response['status'] = $ex->getMessage();
        }

        return $this->getResponse()->representJson(
            $this->jsonHelper->serialize($response)
        );
    }
}
