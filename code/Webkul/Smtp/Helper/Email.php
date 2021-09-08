<?php

/**
 * Webkul_Smtp email helper
 * @category  Webkul
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Smtp\Helper;

use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Webkul Smtp Email helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context  $context,
     * @param tateInterface                          $inlineTranslation,
     * @param TransportBuilder                       $transportBuilder,
     * @param StoreManagerInterface                  $storeManager,
     * @param CustomerRepositoryInterface            $customer,
     * @param AffiliateHelper                        $affiliateHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
    }

    /**
     * [generateTemplate description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate(
        $emailTemplateVariables,
        $senderInfo,
        $receiverInfo,
        $emailTempId
    ) {
        $template =  $this->_transportBuilder->setTemplateIdentifier($emailTempId)->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ]
        )->setTemplateVars($emailTemplateVariables)->setFrom($senderInfo)
        ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

    /**
     * send mail for test approve
     * @param array $testMailData test mail data
     * @return void
     */
    public function sendTestMailForSmtp($testMailData)
    {
        $senderInfo = ['name' =>'From', 'email' => $testMailData['mail-from']];
        $receiverInfo = ['name' => 'To', 'email' => $testMailData['mail-to']];

        $emailTempVariables = ['message' => $testMailData['content']];

        $this->generateTemplate($emailTempVariables, $senderInfo, $receiverInfo, 'smtp_test_email_template');

        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }
}
