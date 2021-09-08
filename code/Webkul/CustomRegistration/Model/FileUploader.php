<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomRegistration\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\FileProcessor;
use Magento\Framework\Filesystem;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;

class FileUploader
{
    public function __construct(
        CustomerMetadataInterface $customerMetadataService,
        AddressMetadataInterface $addressMetadataService,
        ElementFactory $elementFactory,
        FileProcessorFactory $fileProcessorFactory,
        Filesystem $filesystem,
        AttributeMetadataInterface $attributeMetadata,
        $entityTypeCode,
        $scope,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->customerMetadataService = $customerMetadataService;
        $this->addressMetadataService = $addressMetadataService;
        $this->elementFactory = $elementFactory;
        $this->fileProcessorFactory = $fileProcessorFactory;
        $this->filesystem = $filesystem;
        $this->attributeMetadata = $attributeMetadata;
        $this->entityTypeCode = $entityTypeCode;
        $this->scope = $scope;
        $this->request = $request;
    }

    /**
     * Validate uploaded file
     *
     * @return array|bool
     */
    public function validate()
    {
        $formElement = $this->elementFactory->create(
            $this->attributeMetadata,
            null,
            $this->entityTypeCode
        );

        $errors = $formElement->validateValue($this->getData());
        return $errors;
    }

    /**
     * Execute file uploading
     *
     * @return \string[]
     * @throws LocalizedException
     */
    public function upload()
    {
        $allowedExtensions = $this->getAllowedExtensions();
        
        if (count($allowedExtensions) == 0) {
            $allowedExtensions = explode(',', $this->attributeMetadata->getNote());
        }

        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->fileProcessorFactory->create([
            'entityTypeCode' => $this->entityTypeCode,
            'allowedExtensions' => $allowedExtensions,
        ]);

        $result = $fileProcessor->saveTemporaryFile($this->scope . '[' . $this->getAttributeCode() . ']');

        $path = "";
        if (isset($result['path'])) {
            $path = $result['path'].'/';
        } else {
            $path = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath(
                $this->entityTypeCode. '/' . FileProcessor::TMP_DIR
            );
        }
        $result['tmp_name'] = $path . '/' . ltrim($result['file'], '/');

        $result['url'] = $fileProcessor->getViewUrl(
            FileProcessor::TMP_DIR . '/' . ltrim($result['file'], '/'),
            $this->attributeMetadata->getFrontendInput()
        );

        return $result;
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    private function getAttributeCode()
    {
        return key($this->request->getFiles('custom_registration'));
    }

    /**
     * Retrieve data from global $_FILES array
     *
     * @return array
     */
    private function getData()
    {
        $data = $this->request->getFiles('custom_registration');
        if (!empty($data) && isset($data[$this->getAttributeCode()])) {
            return $data[$this->getAttributeCode()];
        }

        return [];
    }
    
    private function getAllowedExtensions()
    {
        $allowedExtensions = [];
        $validationRules = $this->attributeMetadata->getValidationRules();
        foreach ($validationRules as $validationRule) {
            if ($validationRule->getName() == 'file_extensions') {
                $allowedExtensions = explode(',', $validationRule->getValue());
                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });
                break;
            }
        }
        
        return $allowedExtensions;
    }
}
