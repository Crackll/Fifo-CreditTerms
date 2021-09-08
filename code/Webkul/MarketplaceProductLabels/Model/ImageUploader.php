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
namespace Webkul\MarketplaceProductLabels\Model;

use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\FileSystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Webkul\MarketplaceProductLabels\Helper\Data;

class ImageUploader
{
    /**
     * @var string $baseTmpPath
     */
    protected $baseTmpPath = "mplabel/tmp/label";

    /**
     * @var string $basePath
     */
    protected $basePath = "mplabel/label";

    /**
     * @var Array
     */
    protected $allowedExtensions;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $coreFileStorage;

    /**
     * @var \Magento\Framework\FileSystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Webkul\MarketplaceProductLabels\Helper\Data
     */
    protected $helper;

    /**
     * @param Database $coreFileStorage
     * @param FileSystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        Database $coreFileStorage,
        FileSystem $fileSystem,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager,
        Registry $registry,
        LoggerInterface $logger,
        Data $helper
    ) {
        $this->coreFileStorage = $coreFileStorage;
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Set BaseTmpPath
     *
     * @param String $baseTmpPath
     */
    public function setBaseTmpPath($baseTmpPath)
    {
        $this->baseTmpPath = $baseTmpPath;
    }

    /**
     * Set BasePath
     *
     * @param String $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * set allowedExtensions function
     *
     * @param Array $allowedExtensions
     */
    public function setAllowedExtensions($allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * Get BaseTmpPath
     *
     * @return $baseTmpPath
     */
    public function getBaseTmpPath()
    {
        return $this->baseTmpPath;
    }

    /**
     * Get BasePath
     *
     * @return $basePath
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Get Allowed Extensions
     *
     * @return $allowedExtensions
     */
    public function getAllowedExtensions()
    {
        if (empty($this->allowedExtensions)) {
            $this->allowedExtensions = $this->helper->getAllowedImageExtensions();
        }
        return $this->allowedExtensions;
    }

    /**
     * Get FilePath
     *
     * @param String $path
     * @param String $imageName
     *
     * @return String
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }

    /**
     * Move File From Temporary Location function
     *
     * @param String $imageName
     * @param String $url
     *
     * @return $imageName
     */
    public function moveFileFromTmp($imageName, $url)
    {
        $count = 6;
        $count += stripos($url, "/media/");
        $basePath = $this->getBasePath();
        try {
            $baseImagePath = $this->getFilePath($basePath, $imageName);
            $baseTmpImagePath = substr($url, $count);
            $mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            $this->coreFileStorage->copyFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            $mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }
        return $imageName;
    }

    /**
     * Save File to Temporary Location function
     *
     * @param String $fileId
     *
     * @return Array $result
     */
    public function saveFileToTmpDir($fileId)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->getAllowedExtensions());
        $uploader->setAllowRenameFiles(true);
        $mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $result = $uploader->save($mediaDirectory->getAbsolutePath($baseTmpPath));
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
        $result['path'] = str_replace('\\', '/', $result['path']);
        $result['url'] = $this->storeManager
                ->getStore()
                ->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $this->getFilePath($baseTmpPath, $result['file']);
        $result['name'] = $result['file'];
        if (isset($result['file'])) {
            try {
                $relativePath = rtrim($baseTmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->coreFileStorage->saveFile($relativePath);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while saving the file(s).')
                );
            }
        }
        return $result;
    }
}
