<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPushNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPushNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Webkul MpPushNotification PostDispatchConfigSaveObserver Observer.
 */
class PostDispatchConfigSaveObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $_messageManager;
    /**
     * @var IoFile
     */
    protected $_filesystemFile;
    protected $_http;
    protected $storeManager;
    protected $file;

    /**
     * @param ManagerInterface $messageManager
     * @param Filesystem       $filesystem
     * @param HelperData       $helper
     */
    public function __construct(
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        IoFile $filesystemFile,
        File $file,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $http
    ) {
        $this->_messageManager = $messageManager;
        $this->_baseDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_filesystemFile = $filesystemFile;
        $this->file = $file;
        $this->_http = $http;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        try {
            /**
            * @var \Magento\Framework\ObjectManagerInterface $objManager
            */
            $objManager = \Magento\Framework\App\ObjectManager::getInstance();
            /**
             * @var \Magento\Framework\Module\Dir\Reader $reader
            */
            $reader = $objManager->get(\Magento\Framework\Module\Dir\Reader::class);

            /**
             * @var \Magento\Framework\Filesystem $filesystem
            */
            $filesystem = $objManager->get(\Magento\Framework\Filesystem::class);

            $serviceWorkerJsFile = $reader->getModuleDir(
                '',
                'Webkul_MpPushNotification'
            ).'/view/frontend/web/js/firebase-messaging-sw.js';
            
            $serviceWorkerJsDestination = $this->_baseDirectory->getAbsolutePath().'firebase-messaging-sw.js';
            $parts = explode('/', $this->_baseDirectory->getAbsolutePath());
            $last = array_pop($parts);
            $last = array_pop($parts);
            $parts = [implode('/', $parts), $last];
            // $this->_filesystemFile->cp($serviceWorkerJsFile, $parts[0].'/firebase-messaging-sw.js');
            $this->_filesystemFile->cp($serviceWorkerJsFile, $serviceWorkerJsDestination);
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
}
