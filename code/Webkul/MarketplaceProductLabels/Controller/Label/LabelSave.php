<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Controller\Label;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;

class LabelSave extends Action
{
    /**
     * @var \Magento\Framework\Filesystem $fileSystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploader;

    /**
     * @var \Webkul\MarketplaceProductLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MarketplaceProductLabels\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MarketplaceProductLabels\Helper\Email
     */
    protected $email;

    /**
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader
     * @param \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Webkul\MarketplaceProductLabels\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MarketplaceProductLabels\Helper\Email $email
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader,
        \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Webkul\MarketplaceProductLabels\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MarketplaceProductLabels\Helper\Email $email
    ) {
        $this->customerFactory = $customerFactory;
        $this->fileSystem = $fileSystem;
        $this->uploader = $fileUploader;
        $this->labelFactory = $labelFactory;
        $this->fileDriver = $fileDriver;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->email = $email;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MarketplaceProductLabels::grid_list');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $error = false;
        $data = $this->getRequest()->getParams();
        $data['label_name'] = strip_tags(trim($data['label_name']));
        $dimensionManage = $this->helper->getConfigData('label_dimension_manage');
        
        if (!array_key_exists("id", $data) && !$dimensionManage) {
                $data['label_width_productpage'] = $this->helper->getConfigData('defaultWidth_productPage');
                $data['label_height_productpage'] = $this->helper->getConfigData('defaultHeight_productPage');
                $data['label_width_categorypage'] = $this->helper->getConfigData('defaultWidth_categoryPage');
                $data['label_height_categoryage'] = $this->helper->getConfigData('defaultHeight_categoryPage');
        }

        if ($this->getRequest()->isPost()) {
            $id = (int) $this->getRequest()->getParam('id');
            $path = $this->fileSystem
                        ->getDirectoryRead(DirectoryList::MEDIA)
                        ->getAbsolutePath('mplabel/label/');
            $this->fileDriver->createDirectory($path, 0755);
            try {
                $allowedExtensions = $this->helper->getAllowedImageExtensions();
                $uploader = $this->uploader->create(['fileId' => 'image_name']);
                $uploader->setAllowedExtensions($allowedExtensions);
                $imageData = $uploader->validateFile();
                $name = $imageData['name'];
                $ext = substr($name, strrpos($name, '.') + 1);
                $imageName = 'File-'.time().'.'.$ext;
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $uploader->save($path, $imageName);
                if (array_key_exists("id", $data)) {
                    unset($data['image_name']);
                    $data['image_name']=$imageName;
                } else {
                    $data['image_name'] = $imageName;
                }
            } catch (\Exception $e) {
                $error = true;
            }
            if ($id == 0 && $error) {
                $this->messageManager->addError(__("Please select a valid image"));
                return $resultRedirect->setPath('*/label/newlabel/');
            }
            $labelId = $this->setImageData($data);
            $this->messageManager->addSuccess(__('Label saved successfully'));
        } else {
            $this->messageManager->addError(__("Something went wrong"));
        }

        if ($this->helper->getConfigData('label_approval') || $this->helper->getConfigData('label_edit_approval')) {
            $adminStoreEmail = $this->mpHelper->getAdminEmailId();
            $adminEmail = $adminStoreEmail ? $adminStoreEmail : $this->mpHelper->getDefaultTransEmailId();
            $adminUsername = $this->helper->getAdminName();
            $sellerId = $data['seller_id'];
            $seller = $this->customerFactory->create()->load($sellerId);
            $receiverInfo= [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $senderInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];

            $emailTemplateVariables = [];
            $emailTemplateVariables['myvar1'] = $data['label_name'];
            $editFlag = null;
            if (array_key_exists("id", $data)) {
                $editFlag = 1;
                $emailTemplateVariables['myvar2'] = __(
                    'I would like to inform you that recently I have updated a product label in the store.'
                );
            } else {
                $emailTemplateVariables['myvar2'] = __(
                    'I would like to inform you that recently I have added a new product label in the store.'
                );
            }
            $emailTemplateVariables['myvar3'] = $adminUsername;
            
            $this->email->labelApprovalReqMail(
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo,
                $editFlag
            );
        }

        return $this->resultRedirectFactory->create()
                    ->setPath('*/label/labellist/', ['_secure' => $this->getRequest()->isSecure(),]);
    }

    /**
     * Save Image Data
     *
     * @param array $data
     */
    public function setImageData($data)
    {
        $model = $this->labelFactory->create();
        if (array_key_exists("id", $data)) {
            if (isset($data['image_name'])) {
                $imgName=$data['image_name'];
                unset($data['image_name']);
                $data['image_name']=$imgName;
            }
            $model->addData($data)->setId($data['id'])->save();
            return $labelId = $model->getId();
        } else {
            $model->setData($data)->save();
            return $labelId = $model->getId();
        }
    }
}
