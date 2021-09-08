<?php
/**
 * Webkul_Smtp
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Smtp\Model;

use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class Smtp
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
     * @param \Webkul\Smtp\Helper\Data $dataHelper
     * @param \Webkul\Smtp\Model\Store $storeModel
     */
    public function __construct(
        \Webkul\Smtp\Helper\Data $dataHelper,
        \Webkul\Smtp\Model\Store $storeModel
    ) {
        $this->dataHelper = $dataHelper;
        $this->storeModel = $storeModel;
    }

    /**
     * @param \Webkul\Smtp\Helper\Data $dataHelper
     * @return Smtp
     */
    public function setDataHelper(\Webkul\Smtp\Helper\Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
        return $this;
    }

    /**
     * @param \Webkul\Smtp\Model\Store $storeModel
     * @return Smtp
     */
    public function setStoreModel(\Webkul\Smtp\Model\Store $storeModel)
    {
        $this->storeModel = $storeModel;
        return $this;
    }

    /**
     * @param \Magento\Framework\Mail\MessageInterface $message
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendSmtpMessage(
        \Magento\Framework\Mail\EmailMessage $message
    ) {
        $dataHelper = $this->dataHelper;
        $dataHelper->setStoreId($this->storeModel->getStoreId());
        $message = Message::fromString($message->getRawMessage());
        if (empty($message->getFrom()) || count($message->getFrom()) == 0) {
            $result = $this->storeModel->getFrom();
            $message->setFrom($result['email'], $result['name']);
        }
        //set config
        $smtpSetting = $this->dataHelper->getSmtpConfig();
        $options   = new SmtpOptions([
            'name' => 'Admin',
            'host' => $smtpSetting['hosturl'],
            'port' => $smtpSetting['config']['port'],
        ]);

        $connectionConfig = [];
        $auth = strtolower($smtpSetting['config']['auth']);
        if ($auth != 'none') {
            $options->setConnectionClass($auth);
            $connectionConfig = [
                'username' => $smtpSetting['config']['username'],
                'password' => $smtpSetting['config']['password']
            ];
        }
        if (isset($smtpSetting['config']['tsl'])) {
            $tsl = $smtpSetting['config']['tsl'];
            /*if ($tsl != 'none') {
                $connectionConfig['tsl'] = $tsl;
            }*/
        } else {
            $ssl = $smtpSetting['config']['ssl'];
            if ($ssl != 'none') {
                $connectionConfig['ssl'] = $ssl;
            }
        }

        if (!empty($connectionConfig)) {
            $options->setConnectionConfig($connectionConfig);
        }
        try {
            $transport = new SmtpTransport();
            $transport->setOptions($options);
            $transport->send($message);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\MailException(
                new \Magento\Framework\Phrase($e->getMessage()),
                $e
            );
        }
    }
}
