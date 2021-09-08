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

namespace Webkul\WebApplicationFirewall\Helper;

use Magento\Store\Model\Store;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Webkul\WebApplicationFirewall\Helper\Data as BaseHelper;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * WAF module DebugHelper
 */
class DebugHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH = 'waf_setting/';

    /** @var JsonSerializer */
    protected $serializer;

    /** @var array */
    protected $loggers;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        JsonSerializer $serializer,
        $loggers = []
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->loggers = $loggers;
        $this->defaultLogger = $context->getLogger();
    }

    /**
     * Decode string from json to array
     *
     * @param string $jsonString
     * @return array
     * @throws \InvalidArgumentException
     */
    public function jsonDecode($jsonString)
    {
        try {
            return $this->serializer->unserialize($jsonString);
        } catch (\InvalidArgumentException $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * Retutn custom logger
     *
     * @return void
     */
    public function getLogger($type = 'default')
    {
        $logger = $this->defaultLogger;
        foreach ($this->loggers as $key => $customLogger) {
            if ($type == $key) {
                $logger = $customLogger;
            }
        }
        return $logger;
    }

    public function processDebuging($logData, $type = 'default', $mode = 'info')
    {
        $loggers = $this->getLogger($type);
        if ($mode == 'info') {
            if (is_array($logData)) {
                foreach ($logData as $data) {
                    if (is_array($logData)) {
                        $loggers->info('', $logData);
                    } else {
                        $loggers->info($logData);
                    }
                }
            } else {
                $loggers->info($logData);
            }
        }
    }
}
