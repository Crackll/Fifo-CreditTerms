<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_MpSellerBuyerCommunication
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerBuyerCommunication\Logger;

/**
 * MpSellerBuyerCommunication Handler class.
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/sellerbuyercommunication.log';
}
