<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpVendorAttributeManager\Plugin\Customer\Model\Metadata\Form;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\ObjectManager;

class File
{
    protected $_fileSystem;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory
    ) {
        $this->_fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->_logger = $logger;
    }

    public function aroundCompactValue($subject, $proceed, $value)
    {
        $attributeCode = $subject->getAttribute()->getAttributeCode();
        $explodedValue = explode('_', $attributeCode);
        $result = $proceed($value);
        
        if (is_array($explodedValue) && !is_array($result)) {
            if ('wkv' == $explodedValue[0]) {
                $mediaDir = $this->_fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
                if ($result == '') {
                    $destinationPath = $mediaDir->getAbsolutePath(
                        'vendorfiles'.$result
                    );
                    $mediaDir->delete('vendorfiles/' . ltrim($subject->restoreValue($result), '/'));
                } else {
                    $path = $mediaDir->getAbsolutePath(
                        'vendorfiles'.$result
                    );
                    $destinationPath = $mediaDir->getAbsolutePath(
                        'vendorfiles'.$result
                    );
                    $mediaDir->copyFile($path, $destinationPath, $mediaDir);
                }
            }
        }
        return $result;
    }
}
