<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @var int
     */
    protected $loggerType = HelpdeskLogger::INFO;

    /**
     * File name.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @var string
     */
    protected $fileName = '/var/log/helpdesk.log';
}
