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

namespace Webkul\WebApplicationFirewall\Plugin\Magento\Framework\File;

use Magento\Framework\DataObject;

/**
 * Uploader class plugin
 */
class Uploader
{

    /**
     * Notification
     *
     * @var \Webkul\WebApplicationFirewall\Model\Notificator
     */
    protected $notificator;

    /**
     * Helper
     *
     * @var\Webkul\WebApplicationFirewall\Helper\Data
     */
    protected $helper;

    /**
     * @var $request
     */
    protected $request;

    /**
     * @param \Webkul\WebApplicationFirewall\Model\Notificator $notificator
     * @param \Webkul\WebApplicationFirewall\Helper\Data $helper
     */
    public function __construct(
        \Webkul\WebApplicationFirewall\Model\Notificator $notificator,
        \Webkul\WebApplicationFirewall\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->notificator = $notificator;
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * After file upload notification plugin
     *
     * @see \Magento\Framework\File\Uploader::save
     */
    public function afterSave(
        \Magento\Framework\File\Uploader $subject,
        $result,
        $destinationFolder,
        $newFileName = null
    ) {
        $fileUploadNotification = $this->helper->getConfigData(
            'file_security',
            'file_upload_notification'
        );
        if ($fileUploadNotification && $result && is_array($result)) {
            $notificationData = new DataObject($result);
            $toEmail = $this->helper->getConfigData(
                'file_security',
                'file_notification'
            );
            $this->notificator->sendFileUploadNotification(
                $notificationData,
                $toEmail
            );
        }
        return $result;
    }

    /**
     * Prevent config type extension to be upload.
     *
     * @see \Magento\Framework\File\Uploader::checkAllowedExtension
     */
    public function afterCheckAllowedExtension(
        \Magento\Framework\File\Uploader $subject,
        $result,
        $extension
    ) {
        $files = [];
        $fileNames = [];

        $isMultiExtension = $this->helper->getConfigData(
            'file_security',
            'check_multi_extension'
        );

        if ($isMultiExtension) {
            try {
                $files = $this->request->getFiles()->toArray();
            } catch (\Exception $e) {
                $e->getMessage();
            }

            if (!empty($files)) {
                $fileNames = array_column($files, 'name');
                $fileNames = array_map(function ($val) {
                    return strtolower($val);
                }, $fileNames);
            }
        }

        $notAllowedExtension = $this->helper->getConfigData(
            'file_security',
            'prevent_file_extension'
        );
        if ($notAllowedExtension) {
            $notAllowedExtension = strtolower($notAllowedExtension);
            $extension = strtolower($extension);
            $notAllowedExtension = explode(',', $notAllowedExtension);
            if (in_array($extension, $notAllowedExtension)) {
                $data = [
                    'type' => '.'.$extension
                ];
                $toEmail = $this->helper->getConfigData(
                    'file_security',
                    'file_notification'
                );
                $notificationData = new DataObject($data);
                $this->notificator->sendMalaciousFileUploadNotification(
                    $notificationData,
                    $toEmail
                );
                $result = false;
            } elseif ($isMultiExtension) {
                foreach ($fileNames as $fileName) {
                    $innerExtensions = $this->getExtractInnerExtensions($fileName, $extension);
                    foreach ($innerExtensions as $innerExtension) {
                        if (in_array($innerExtension, $notAllowedExtension)) {
                            $data = [
                                'type' => '.'.$innerExtension
                            ];
                            $toEmail = $this->helper->getConfigData(
                                'file_security',
                                'file_notification'
                            );
                            $notificationData = new DataObject($data);
                            $this->notificator->sendMalaciousFileUploadNotification(
                                $notificationData,
                                $toEmail
                            );
                            $result = false;
                            return $result;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * get Extract Inner Extensions
     * @param string $fileName
     * @return array
     */
    private function getExtractInnerExtensions($fileName, $extractFor)
    {
        $pieces = explode(".", $fileName);
        $extensions = [];
        $num = count($pieces);
        if (($num > 2) && $pieces[$num-1] == $extractFor) {
            for ($i=1; $i < $num-1; $i++) {
                $extensions[] = strtolower($pieces[$i]);
            }
        }

        return $extensions;
    }
}
