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
namespace Webkul\MarketplaceProductLabels\Controller\Adminhtml\Image;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MarketplaceProductLabels\Model\ImageUploaderFactory;

class UploadImage extends Action
{
    /**
     * @var \Webkul\MarketplaceProductLabels\Model\ImageUploaderFactory
     */
    protected $imageUploaderFactory;

    /**
     * @param Context $context
     * @param ImageUploader $imageUploaderFactory
     */
    public function __construct(
        Context $context,
        ImageUploaderFactory $imageUploaderFactory
    ) {
        parent::__construct($context);
        $this->imageUploaderFactory = $imageUploaderFactory;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $files = $this->getRequest()->getFiles();
            $result = $this->imageUploaderFactory->create()->saveFileToTmpDir($files['labels']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
