<?php
/**
 * Webkul_Smtp
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Smtp\Plugin\Mail;

class TransportPlugin extends \Zend_Mail_Transport_Smtp
{
    /**
     * @var \Webkul\Smtp\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Webkul\Smtp\Model\Store
     */
    private $storeModel;

    /**
     * @var \Webkul\Smtp\Logger\Logger
     */
    private $logger;

    /**
     * @param \Webkul\Smtp\Helper\Data $dataHelper
     * @param \Webkul\Smtp\Model\Store $storeModel
     * @param \Webkul\Smtp\Logger\Logger $logger
     */
    public function __construct(
        \Webkul\Smtp\Helper\Data $dataHelper,
        \Webkul\Smtp\Model\Store $storeModel,
        \Webkul\Smtp\Logger\Logger $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->storeModel = $storeModel;
        $this->logger = $logger;
    }
    /**
     * @param \Magento\Framework\Mail\TransportInterface $subject
     * @param \Closure $proceed
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Zend_Mail_Exception
     */
    public function aroundSendMessage(
        \Magento\Framework\Mail\TransportInterface $subject,
        \Closure $proceed
    ) {
        try {
            if ($this->dataHelper->isEnable()) {
                if (method_exists($subject, 'getStoreId')) {
                    $this->storeModel->setStoreId($subject->getStoreId());
                }
                $message = $subject->getMessage();
                $smtpSetting = $this->dataHelper->getSmtpConfig();
                $smtp = new \Webkul\Smtp\Model\Smtp($this->dataHelper, $this->storeModel);
                $smtp->sendSmtpMessage($message);
            } else {
                $proceed();
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
        }
    }
}
