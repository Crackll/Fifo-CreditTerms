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

namespace Webkul\RegionUpload\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class MoveMediaFiles implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * Undocumented function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Reader $reader
     * @param Filesystem $filesSystem
     * @param File $fileDriver
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Reader $reader,
        Filesystem $filesSystem,
        File $fileDriver
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_reader = $reader;
        $this->_filesystem = $filesSystem;
        $this->_fileDriver = $fileDriver;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moveDirToMediaDir();
    }

    /**
     * Copy Sample CSV,XML and XSL files to Media
     */
    private function moveDirToMediaDir()
    {
        try {
            $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $smpleFilePath = $this->_filesystem->getDirectoryRead($type)
                            ->getAbsolutePath().'sample/';
            $file = 'sample.csv';
            if ($this->_fileDriver->isExists($smpleFilePath)) {
                $this->_fileDriver->deleteDirectory($smpleFilePath);
            }
            if (!$this->_fileDriver->isExists($smpleFilePath)) {
                $this->_fileDriver->createDirectory($smpleFilePath, 0777);
            }
            $filePath = $smpleFilePath.$file;
            if (!$this->_fileDriver->isExists($filePath)) {
                $path = '/sample/'.$file;
                $mediaFile = $this->_reader->getModuleDir('', 'Webkul_RegionUpload').$path;
                if ($this->_fileDriver->isExists($mediaFile)) {
                    $this->_fileDriver->copy($mediaFile, $filePath);
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
