<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Helper;

use Magento\Framework\Exception\MailException;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param String NewLabelApproveRequestTemplate
     */
    const XML_PATH_EMAIL_NEW_LABEL = 'mpproductlabel/email/new_label_approve_request';

    /**
     * @param String EditLabelApproveRequestTemplate
     */
    const XML_PATH_EMAIL_EDIT_LABEL = 'mpproductlabel/email/edit_label_approve_request';

    /**
     * @param String LabelApproveTemplate
     */
    const XML_PATH_EMAIL_LABEL_APPROVAL = 'mpproductlabel/email/label_approve';

    /**
     * @param String LabelDisapproveTemplate
     */
    const XML_PATH_EMAIL_LABEL_DISAPPROVAL = 'mpproductlabel/email/label_disapprove';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $template;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }
    
    /**
     * Send LabelUnapproveMail
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendLabelDisApprovalMail($emailTemplateVariables, $senderInfo, $receiverInfo, $storeId = 0)
    {
        $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_LABEL_DISAPPROVAL, $storeId);

        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo, $storeId);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Send LabelApproveMail
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendLabelApprovalMail($emailTemplateVariables, $senderInfo, $receiverInfo, $storeId)
    {
        $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_LABEL_APPROVAL, $storeId);

        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo, $storeId);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Send NewLabelApprovalRequestMail
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function labelApprovalReqMail($emailTemplateVariables, $senderInfo, $receiverInfo, $editFlag)
    {
        if ($editFlag == null) {
            $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_NEW_LABEL);
        } else {
            $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_EDIT_LABEL);
        }

        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath, $storeId = '')
    {
        if (!empty($storeId)) {
            return $this->getConfigValue($xmlPath, $storeId);
        }
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
     * Generate Template
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo, $storeId = '')
    {
        if (!empty($storeId)) {
            $setStoreId = $storeId;
        } else {
            $setStoreId = $this->storeManager->getStore()->getId();
        }
        $senderEmail = $senderInfo['email'];
        $adminEmail = $this->getConfigValue(
            'trans_email/ident_general/email',
            $this->getStore()->getStoreId()
        );
        $senderInfo['email'] = $adminEmail;
        $template = $this->transportBuilder->setTemplateIdentifier($this->template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $setStoreId,
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name'])
            ->setReplyTo($senderEmail, $senderInfo['name']);
        return $this;
    }
}
