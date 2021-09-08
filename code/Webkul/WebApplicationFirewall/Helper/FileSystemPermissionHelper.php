<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Helper;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\App\State;

/**
 * WAF FileSystemPermissionHelper
 */
class FileSystemPermissionHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * directories permission in default or developer mode under shared hosting
     */
    const DIRECTORY_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING = '755';

    /**
     * files permission in default or developer mode under shared hosting
     */
    const FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING = '644';

    /**
     * directories permission in production mode under shared hosting
     */
    const DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING = '755';

    /**
     * files permission in production mode under shared hosting
     */
    const FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING = '644';

    /**
     * specific directories permission in production mode under shared hosting
     */
    const SPECIFIC_DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING = '555';

    /**
     * specific files permission in production mode under shared hosting
     */
    const SPECIFIC_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING = '444';

    /**
     * magento executable file permission in default or developer mode under shared hosting
     */
    const BIN_MAGENTO_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING = '755';

    /**
     * magento executable file permission in production mode under shared hosting
     */
    const BIN_MAGENTO_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING = '555';

    /**
     * db environment file permission in default or developer mode under shared hosting
     */
    const ENV_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING = '640';

    /**
     * db environment file permission in production mode under shared hosting
     */
    const ENV_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING = '440';

    /**
     * directories permission in default or developer mode under private hosting
     */
    const DIRECTORY_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING = '775';

    /**
     * files permission in default or developer mode under private hosting
     */
    const FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING = '664';

    /**
     * directories permission in production mode under private hosting
     */
    const DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING = '755';

    /**
     * files permission in production mode under shared hosting
     */
    const FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING = '644';

    /**
     * specific directories permission in production mode under private hosting
     */
    const SPECIFIC_DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING = '755';

    /**
     * specific files permission in production mode under private hosting
     */
    const SPECIFIC_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING = '644';

    /**
     * magento executable file permission in default or developer mode under private hosting
     */
    const BIN_MAGENTO_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING = '775';

    /**
     * magento executable file permission in production mode under private hosting
     */
    const BIN_MAGENTO_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING = '755';

    /**
     * db environment file permission in default or developer mode under private hosting
     */
    const ENV_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING = '660';

    /**
     * db environment file permission in production mode under private hosting
     */
    const ENV_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING = '640';

    /**
     * @var $file
     */
    private $file;

    /**
     * @var $jsonHelper
     */
    private $jsonHelper;

    /**
     * @var $ioFile
     */
    private $ioFile;

    /**
     * @var $appState
     */
    private $appState;

    /**
     * Constructor
     * @param Magento\Framework\App\Helper\Context $context
     * @param File                                 $file
     * @param Json                                 $jsonHelper
     * @param IoFile                               $ioFile
     * @param State                                $appState
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        File $file,
        Json $jsonHelper,
        IoFile $ioFile,
        State $appState
    ) {
        parent::__construct($context);
        $this->file = $file;
        $this->jsonHelper = $jsonHelper;
        $this->ioFile = $ioFile;
        $this->appState = $appState;
    }

    /**
     * get base url
     * @param void
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->file->getRealPath('.');
    }

    /**
     * get files and directories with information
     * @param string $path
     * @param string $serverType
     * @return array
     */
    public function getReadDirectoryWithInfo($path, $serverType)
    {
        $data = [];
        $baseUrl = $this->getBaseUrl();
        $realPath = $this->file->getRealPath($path);

        if (strpos($realPath, $baseUrl) !== 0) {
            return false;
        }

        $path = ".".substr($realPath, strlen($baseUrl));

        $dirs = $this->file->readDirectory($path);
        $magentoMode = $this->getMagentoMode();
        foreach ($dirs as $dir) {
            $temp = [];
            $mode = $this->file->stat($dir)['mode'];
            $mode = decoct($mode & 0777);

            $type = 'unknown';
            if ($this->file->isDirectory($dir)) {
                $type = 'directory';
            } elseif ($this->file->isFile($dir)) {
                $type = 'file';
            }

            $permission = $this->getPermissionInCharFormat($mode, $type);
            $pathInfo = $this->ioFile->getPathInfo($dir);

            $temp['type'] = $type;
            $temp['path'] = $dir;
            $temp['basename'] = $pathInfo['basename'];
            $temp['mode'] = $mode;
            $temp['permission'] = $permission;

            $response = $this->getFilesAndDirectoriesStatus(
                $temp,
                $magentoMode,
                $serverType
            );

            $temp['response'] = $response;

            $data[] = $temp;
        }

        return $data;
    }

    /**
     * get files and directories status
     * @param array $data
     * @param string $magentoMode
     * @param string $serverType
     * @return array
     */
    public function getFilesAndDirectoriesStatus($data, $magentoMode, $serverType)
    {
        $response = [];

        if ($serverType == 'shared') {
            $response = $this->getStatusForSharedHosting($data, $magentoMode);
        } elseif ($serverType == 'private') {
            $response = $this->getStatusForPrivateHosting($data, $magentoMode);
        }

        return $response;
    }

    /**
     * get files and directories status for shared hosting
     * @param array $data
     * @param string $magentoMode
     * @return array
     */
    public function getStatusForSharedHosting($data, $magentoMode)
    {
        $response = [];
        if ($magentoMode == \Magento\Framework\App\State::MODE_DEFAULT ||
            $magentoMode == \Magento\Framework\App\State::MODE_DEVELOPER
        ) {
            $response = $this->getStatusInDefaultOrDeveloperModeUnderSharedHosting($data);
        } else {
            $response = $this->getStatusInProductionModeUnderSharedHosting($data);
        }

        return $response;
    }

    /**
     * get files and directories status for private hosting
     * @param array $data
     * @param string $magentoMode
     * @return array
     */
    public function getStatusForPrivateHosting($data, $magentoMode)
    {
        $response = [];
        if ($magentoMode == \Magento\Framework\App\State::MODE_DEFAULT ||
            $magentoMode == \Magento\Framework\App\State::MODE_DEVELOPER
        ) {
            $response = $this->getStatusInDefaultOrDeveloperModeUnderPrivateHosting($data);
        } else {
            $response = $this->getStatusInProductionModeUnderPrivateHosting($data);
        }

        return $response;
    }

    /**
     * get files and directories status for shared hosting under default or developer mode
     * @param array $data
     * @return array
     */
    public function getStatusInDefaultOrDeveloperModeUnderSharedHosting($data)
    {
        $response = [];

        if ($data['type'] == 'directory') {
            $response = $this->getStatusForDirectoriesInDefaultOrDeveloperModeUnderSharedHosting($data);
        } elseif ($data['type'] == 'file') {
            $response = $this->getStatusForFilesInDefaultOrDeveloperModeUnderSharedHosting($data);
        }

        return $response;
    }

    /**
     * get files and directories status for private hosting under default or developer mode
     * @param array $data
     * @return array
     */
    public function getStatusInDefaultOrDeveloperModeUnderPrivateHosting($data)
    {
        $response = [];

        if ($data['type'] == 'directory') {
            $response = $this->getStatusForDirectoriesInDefaultOrDeveloperModeUnderPrivateHosting($data);
        } elseif ($data['type'] == 'file') {
            $response = $this->getStatusForFilesInDefaultOrDeveloperModeUnderPrivateHosting($data);
        }

        return $response;
    }

    /**
     * get files status in default or developer mode under shared hosting
     * @param array $data
     * @return array
     */
    public function getStatusForFilesInDefaultOrDeveloperModeUnderSharedHosting($data)
    {
        $response = [];
        $response['status'] = false;

        switch ($data['path']) {
            case './bin/magento':
                if ($data['mode'] ==
                    self::BIN_MAGENTO_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING
                ) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = __(
                        'Permission should be %1',
                        self::BIN_MAGENTO_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING
                    );
                }
                break;

            case './app/etc/env.php':
                if ($data['mode'] == self::ENV_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = __(
                        'Permission should be %1',
                        self::ENV_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING
                    );
                }
                break;

            default:
                if ($data['mode'] == self::FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = __(
                        'Permission should be %1',
                        self::FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING
                    );
                }
        }

        return $response;
    }

    /**
     * get files status in default or developer mode under private hosting
     * @param array $data
     * @return array
     */
    public function getStatusForFilesInDefaultOrDeveloperModeUnderPrivateHosting($data)
    {
        $response = [];
        $response['status'] = false;

        switch ($data['path']) {
            case './bin/magento':
                if ($data['mode'] ==
                    self::BIN_MAGENTO_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING
                ) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = __(
                        'Permission should be %1',
                        self::BIN_MAGENTO_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING
                    );
                }
                break;

            case './app/etc/env.php':
                if ($data['mode'] == self::ENV_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = __(
                        'Permission should be %1',
                        self::ENV_FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING
                    );
                }
                break;

            default:
                if ($data['mode'] == self::FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING) {
                    $response['status'] = true;
                } else {
                    $response['status'] = false;
                    $response['message'] = __(
                        'Permission should be %1',
                        self::FILE_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING
                    );
                }
        }

        return $response;
    }

    /**
     * get directories status in default or developer mode under shared hosting
     * @param array $data
     * @return array
     */
    public function getStatusForDirectoriesInDefaultOrDeveloperModeUnderSharedHosting($data)
    {
        $response = [];

        if ($data['mode'] == self::DIRECTORY_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING) {
            $response['status'] = true;
        } else {
            $response['status'] = false;
            $response['message'] = __(
                'Permission should be %1',
                self::DIRECTORY_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_SHARED_HOSTING
            );
        }

        return $response;
    }

    /**
     * get directories status in default or developer mode under private hosting
     * @param array $data
     * @return array
     */
    public function getStatusForDirectoriesInDefaultOrDeveloperModeUnderPrivateHosting($data)
    {
        $response = [];

        if ($data['mode'] == self::DIRECTORY_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING) {
            $response['status'] = true;
        } else {
            $response['status'] = false;
            $response['message'] = __(
                'Permission should be %1',
                self::DIRECTORY_PERMISSION_IN_DEFAULT_OR_DEVELOPER_MODE_UNDER_PRIVATE_HOSTING
            );
        }

        return $response;
    }

    /**
     * get files and directories status in production mode under shared hosting
     * @param array $data
     * @return array
     */
    public function getStatusInProductionModeUnderSharedHosting($data)
    {
        $response = [];

        if ($data['type'] == 'directory') {
            $response = $this->getStatusForDirectoriesInProductionModeUnderSharedHosting($data);
        } elseif ($data['type'] == 'file') {
            $response = $this->getStatusForFilesInProductionModeUnderSharedHosting($data);
        }

        return $response;
    }

    /**
     * get files and directories status in production mode under private hosting
     * @param array $data
     * @return array
     */
    public function getStatusInProductionModeUnderPrivateHosting($data)
    {
        $response = [];

        if ($data['type'] == 'directory') {
            $response = $this->getStatusForDirectoriesInProductionModeUnderPrivateHosting($data);
        } elseif ($data['type'] == 'file') {
            $response = $this->getStatusForFilesInProductionModeUnderPrivateHosting($data);
        }

        return $response;
    }

    /**
     * get directories status in production mode under shared hosting
     * @param array $data
     * @return array
     */
    public function getStatusForDirectoriesInProductionModeUnderSharedHosting($data)
    {
        $response = [];

        if ($this->isFileOrDirectoryUnderSpecialDirectoryUnderSharedHosting($data['path'])) {
            if ($data['mode'] == self::SPECIFIC_DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::SPECIFIC_DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING
                );
            }
        } else {
            if ($data['mode'] == self::DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING
                );
            }
        }

        return $response;
    }

    /**
     * get directories status in production mode under private hosting
     * @param array $data
     * @return array
     */
    public function getStatusForDirectoriesInProductionModeUnderPrivateHosting($data)
    {
        $response = [];

        if ($this->isFileOrDirectoryUnderSpecialDirectoryUnderPrivateHosting($data['path'])) {
            if ($data['mode'] == self::SPECIFIC_DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::SPECIFIC_DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING
                );
            }
        } else {
            if ($data['mode'] == self::DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::DIRECTORY_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING
                );
            }
        }

        return $response;
    }

    /**
     * get files status in production mode under shared hosting
     * @param array $data
     * @return array
     */
    public function getStatusForFilesInProductionModeUnderSharedHosting($data)
    {
        $response = [];
        if ($data['path'] == './bin/magento') {
            if ($data['mode'] == self::BIN_MAGENTO_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::BIN_MAGENTO_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING
                );
            }
        } elseif ($data['path'] == './app/etc/env.php') {
            if ($data['mode'] == self::ENV_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::ENV_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING
                );
            }
        } elseif ($this->isFileOrDirectoryUnderSpecialDirectoryUnderSharedHosting($data['path'])) {
            if ($data['mode'] == self::SPECIFIC_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::SPECIFIC_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING
                );
            }
        } else {
            if ($data['mode'] == self::FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_SHARED_HOSTING
                );
            }
        }

        return $response;
    }

    /**
     * get files status in production mode under private hosting
     * @param array $data
     * @return array
     */
    public function getStatusForFilesInProductionModeUnderPrivateHosting($data)
    {
        $response = [];
        if ($data['path'] == './bin/magento') {
            if ($data['mode'] == self::BIN_MAGENTO_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::BIN_MAGENTO_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING
                );
            }
        } elseif ($data['path'] == './app/etc/env.php') {
            if ($data['mode'] == self::ENV_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::ENV_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING
                );
            }
        } elseif ($this->isFileOrDirectoryUnderSpecialDirectoryUnderSharedHosting($data['path'])) {
            if ($data['mode'] == self::SPECIFIC_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::SPECIFIC_FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING
                );
            }
        } else {
            if ($data['mode'] == self::FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = __(
                    'Permission should be %1',
                    self::FILE_PERMISSION_IN_PRODUCTION_MODE_UNDER_PRIVATE_HOSTING
                );
            }
        }

        return $response;
    }

    /**
     * check that files or directories have special permission in production mode under shared hosting
     * @param string $path
     * @return boolean
     */
    public function isFileOrDirectoryUnderSpecialDirectoryUnderSharedHosting($path)
    {
        $response = false;
        $specialDirectories = $this->getSpecialDirectoriesInProductionModeUnderSharedHosting();
        foreach ($specialDirectories as $specialDirectory) {
            if (strpos($path, $specialDirectory) === 0) {
                $response = true;
                break;
            }
        }

        return $response;
    }

    /**
     * check that files or directories have special permission in production mode under private hosting
     * @param string $path
     * @return boolean
     */
    public function isFileOrDirectoryUnderSpecialDirectoryUnderPrivateHosting($path)
    {
        $response = false;
        $specialDirectories = $this->getSpecialDirectoriesInProductionModeUnderPrivateHosting();
        foreach ($specialDirectories as $specialDirectory) {
            if (strpos($path, $specialDirectory) === 0) {
                $response = true;
                break;
            }
        }

        return $response;
    }

    /**
     * get special directories in production mode under shared hosting
     * @param void
     * @return array
     */
    private function getSpecialDirectoriesInProductionModeUnderSharedHosting()
    {
        return $directories = [
            './vendor',
            './app/code',
            './app/etc',
            './pub/static',
            './generated/code',
            './generated/metadata',
            './var/view_preprocessed'
        ];
    }

    /**
     * get special directories in production mode under private hosting
     * @param void
     * @return array
     */
    private function getSpecialDirectoriesInProductionModeUnderPrivateHosting()
    {
        return $directories = [
            './vendor',
            './app/code',
            './app/etc',
            './lib',
            './pub/static',
            './generated/code',
            './generated/metadata',
            './var/view_preprocessed'
        ];
    }

    /**
     * get Serialize
     * @param string data
     * @return string
     */
    public function getSerialize($data)
    {
        return $this->jsonHelper->serialize($data);
    }

    /**
     * get Parent Directory Path
     * @param string $path
     * @param string $serverType
     * @return array
     */
    public function getParentDirectory($path, $serverType)
    {
        $baseUrl = $this->getBaseUrl();
        $realPathForCurrent = $this->file->getRealPath($path);
        $pathForCurrent = ".".substr($realPathForCurrent, strlen($baseUrl));
        if ($pathForCurrent == '.') {
            return false;
        }

        $path = $this->file->getParentDirectory($path);
        $realPath = $this->file->getRealPath($path);
        if (strpos($realPath, $baseUrl) !== 0) {
            return false;
        }

        $path = ".".substr($realPath, strlen($baseUrl));
        $magentoMode = $this->getMagentoMode();
        $mode = $this->file->stat($pathForCurrent)['mode'];
        $mode = decoct($mode & 0777);
        $type = 'directory';
        $permission = $this->getPermissionInCharFormat($mode, $type);

        $temp = [];
        $temp['type'] = $type;
        $temp['path'] = $pathForCurrent;
        $temp['basename'] = "..";
        $temp['mode'] = $mode;
        $temp['permission'] = $permission;

        $response = $this->getFilesAndDirectoriesStatus(
            $temp,
            $magentoMode,
            $serverType
        );

        $temp['path'] = $path;
        $temp['response'] = $response;

        return $temp;
    }

    /**
     * get Permission in Characters Format
     * @param string $filePermission
     * @param string $type
     * @return string
     */
    private function getPermissionInCharFormat($filePermission, $type)
    {
        $info = "-";

        if ($type == 'directory') {
            $info = "d";
        }

        for ($i = 0; $i < 3; $i++) {
            $info .= $this->getPermissionForEachUser($filePermission[$i]);
        }

        return $info;
    }

    /**
     * get permission for each user
     * @param string $permission
     * @return string
     */
    private function getPermissionForEachUser($permission)
    {
        $info = "";
        switch ($permission) {
            case 0:
                $info .= "---"; // No Permission
                break;

            case 1:
                $info .= "--x"; // Execute
                break;

            case 2:
                $info .= "-w-"; // Write
                break;

            case 3:
                $info .= "-wx"; // Write + Execute
                break;

            case 4:
                $info .= "r--"; // Read
                break;

            case 5:
                $info .= "r-x"; // Read + Execute
                break;

            case 6:
                $info .= "rw-"; // Read + Write
                break;

            case 7:
                $info .= "rwx"; // Read + Write + Execute
                break;

            default:
                $info .= "uuu"; // Unknown
                break;
        }

        return $info;
    }

    /**
     * get Magento Mode
     * @param void
     * @return string
     */
    public function getMagentoMode()
    {
        return $this->appState->getMode();
    }
}
