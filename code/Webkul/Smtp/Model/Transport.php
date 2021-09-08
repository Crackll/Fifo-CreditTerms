<?php
/**
 * Webkul_Smtp Transport
 * @category Webkul
 * @package Webkul_Smtp
 * @author Webkul Software Private Limited
 */
namespace Webkul\Smtp\Model;

use Magento\Framework\Mail\MessageInterface;
use Webkul\Smtp\Logger\Logger;

class Transport extends \Zend_Mail_Transport_Sendmail implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var \Magento\Framework\Mail\MessageInterface
     */
    private $message;

    /**
     * @var \Webkul\Smtp\Logger\Logger
     */
    private $logger;

    /**
     * @param  MessageInterface $message
     * @param  \Webkul\Smtp\Logger\Logger $logger
     * @throws \InvalidArgumentException
     */
    public function __construct(
        MessageInterface $message,
        Logger $logger,
        $parameters = null
    ) {
        if (!$message instanceof \Zend_Mail) {
            throw new \InvalidArgumentException('The message should be an instance of \Zend_Mail');
        }
        $this->message = $message;
        $this->logger = $logger;
        parent::__construct($parameters);
    }

    /**
     * Send a mail using this transport
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendMessage()
    {
        try {
            parent::send($this->message);
        } catch (\Exception $e) {
            $this->logger->addInfo(new \Magento\Framework\Phrase($e->getMessage()));
            throw new \Magento\Framework\Exception\MailException(new \Magento\Framework\Phrase($e->getMessage()), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }
}
