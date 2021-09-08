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
namespace Webkul\RegionUpload\Helper;

use \Magento\Framework\File\Csv;
use \Magento\MediaStorage\Model\File\UploaderFactory;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{
    /**
     * Constructor
     *
     * @param Csv $csv
     * @param UploaderFactory $fileUploaderFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Csv $csv,
        UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->csv = $csv;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get Csv File Data in array form
     * @param string $file
     *
     * @return array $csvData
     */
    public function getCsvData($file)
    {
        $csvData = $this->csv->getData($file['wk_region_upload']['tmp_name']);
        return $csvData;
    }

    /**
     * Validate file
     *
     * @return array $result
     */
    public function validateUploadedFiles()
    {
        try {
            $csvUploader = $this->fileUploaderFactory->create(['fileId' => 'wk_region_upload']);
            $csvUploader->setAllowedExtensions(['csv']);
            $validateData = $csvUploader->validateFile();
            $extension = $csvUploader->getFileExtension();
            $csvFilePath = $validateData['tmp_name'];
            $csvFile = $validateData['name'];
            $csvFile = $this->getValidName($csvFile);
            $result = [
                'error' => false,
                'path' => $csvFilePath,
                'csv' => $csvFile,
                'extension' => $extension
            ];
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Remove Special Characters From String
     *
     * @param string $string
     *
     * @return string
     */
    public function getValidName($string)
    {
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        return preg_replace('/-+/', '-', $string);
    }

    /**
     * Get Sample Csv File Url.
     *
     * @return string $result
     */
    public function getSampleCsv()
    {
        $result = [];
        $mediaDirectory = $this->storeManager
                                ->getStore()
                                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $url = $mediaDirectory.'sample/';
        $result = $url.'sample.csv';
        return $result;
    }
}
