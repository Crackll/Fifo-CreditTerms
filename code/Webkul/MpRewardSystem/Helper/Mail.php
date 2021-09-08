<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Helper;

class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_MPREWARDSYSTEM_TRANSACTION = 'mprewardsystem/email_settings/mprewardsystem_transaction';
    const XML_PATH_EMAIL_STATUS_MPREWARDSYSTEM_TRANSACTION = 'mprewardsystem/email_settings/        
     enable_mprewardsystem_transaction_mail';
    /**
     * @var templateId
     */
    protected $tempId;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Webkul\MpRewardSystem\Logger\Logger
     */
    protected $logger;
    /**
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Webkul\MpRewardSystem\Logger\Logger               $logger
     * @param \Magento\Framework\Message\ManagerInterface        $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpRewardSystem\Logger\Logger $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /*transaction email template*/
    /**
     * [sendMail description].
     *
     * @param Mixed $receiverInfo
     * @param Mixed $senderInfo
     * @param Mixed $adminMsg
     * @param Mixed $totalPoints
     */
    public function sendMail($receiverInfo, $senderInfo, $msg, $totalPoints)
    {
        if ($this->getConfigValue(
            'mprewardsystem/email_settings/enable_mprewardsystem_transaction_mail',
            $this->getStore()->getStoreId()
        )) {
            $emailTempVariables = [];
            $emailTempVariables['customername'] = $receiverInfo['name'];
            $emailTempVariables['transactiondetails'] = $msg;
            $emailTempVariables['remainingdetails'] = __(
                "Now total remaining reward points in your account are: %1",
                $totalPoints
            );
            $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_MPREWARDSYSTEM_TRANSACTION);
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            try {
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->messageManager->addError("Unable To Send Mail");
            }
            $this->inlineTranslation->resume();
        }
    }

    /*transaction email template*/
    /**
     * [sendSellerMail description].
     *
     * @param Mixed $receiverInfo
     * @param Mixed $senderInfo
     * @param Mixed $adminMsg
     * @param Mixed $totalPoints
     */
    public function sendSellerMail($sellerReceiverInfo, $receiverInfo, $senderInfo, $adminMsg, $totalPoints)
    {
        if ($this->getConfigValue(
            'mprewardsystem/email_settings/enable_mprewardsystem_transaction_mail',
            $this->getStore()->getStoreId()
        )) {
            $emailTempVariables = [];
            $emailTempVariables['customername'] = $sellerReceiverInfo['name'];
            $emailTempVariables['transactiondetails'] = $receiverInfo['name'] . " " . $adminMsg;
            $emailTempVariables['remainingdetails'] = __(
                "Now total remaining reward points in his/her account are: %1",
                $totalPoints
            );
            $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_MPREWARDSYSTEM_TRANSACTION);
            $this->inlineTranslation->suspend();
            $this->generateTemplate($emailTempVariables, $senderInfo, $sellerReceiverInfo);
            try {
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->messageManager->addError("Unable To Send Mail");
            }
            $this->inlineTranslation->resume();
        }
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    protected function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->transportBuilder->setTemplateIdentifier($this->tempId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }
}
