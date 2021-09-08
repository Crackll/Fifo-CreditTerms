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

namespace Webkul\WebApplicationFirewall\Controller\Adminhtml\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * WAF Download class
 */
class Download extends \Magento\Backend\App\Action
{
    protected $tar = 'Archive/Tar.php';
    protected $resultPageFactory;
    protected $jsonHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\WebApplicationFirewall\Helper\Data $dataHelper,
        DirectoryList $directoryList
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dataHelper = $dataHelper;
        $this->_directoryList    = $directoryList;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $licenceKey = $this->dataHelper->getConfigData('country_ban', 'licence_key');
        if ($licenceKey == '') {
            $response->setError(true);
            $response->setMessages(__('Licence key required to download geoip2 library.'));
            return $this->jsonResponse($response);
        }
        try {
            $path = $this->_directoryList->getPath('media') . '/WAF/GeoIp/GeoIp';
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (!file_exists($path)) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                mkdir($path, 0777, true);
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $folder   = scandir($path, true);

            $pathFile = $path . DIRECTORY_SEPARATOR . $folder[0] . '/GeoLite2-City.mmdb';
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (file_exists($pathFile)) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                foreach (scandir($path . DIRECTORY_SEPARATOR . $folder[0], true) as $filename) {
                    if ($filename == '..' || $filename == '.') {
                        continue;
                    }
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    unlink($path . DIRECTORY_SEPARATOR . $folder[0] . DIRECTORY_SEPARATOR . $filename);
                }
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                rmdir($path . DIRECTORY_SEPARATOR . $folder[0]);
            }
            $url = "https://download.maxmind.com/app/geoip_download";
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            file_put_contents(
                $path . '/GeoLite2-City.tar.gz',
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                fopen("$url?edition_id=GeoLite2-City&license_key=$licenceKey&suffix=tar.gz", 'r')
            );

            // phpcs:disable
            require_once $this->tar;
            // phpcs:enable
            $phar = new \Archive_Tar($path . '/GeoLite2-City.tar.gz', true);
            $phar->extract($path);
            $status  = true;
            $message = __('Download library success!');

            $response->setMessages($message);
            return $this->jsonResponse($response);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response->setError(true);
            $response->setMessages($e->getMessage());
            return $this->jsonResponse($response);
        } catch (\Exception $e) {
            $response->setError(true);
            $response->setMessages(__('401 Unauthorized'));
            return $this->jsonResponse($response);
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
