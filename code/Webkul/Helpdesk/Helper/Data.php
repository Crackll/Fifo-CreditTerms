<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Helper;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Area;

/**
 * Webkul Helpdesk Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\Model\Auth\Session $authSession,
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        parent::__construct($context);
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->_authSession = $authSession;
        $this->_appState = $appState;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
    }

    //get Config thread limit setting
    public function canAgentSeeTickets()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/agent_ticket_visibilty',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTicketDefaultStatus()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketdeafult/defaultstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTicketDefaultGroup()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketdeafult/defaultgroup',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTicketDefaultPriority()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketdeafult/defaultpriority',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTicketDefaultType()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketdeafult/defaulttype',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getAllowedActivity()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/activity/allowedactivity',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getActivityPriorityEdit()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/activity/activitypriorityonedit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getActivityPriorityAdd()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/activity/activitypriorityonadd',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getActivityPriorityDelete()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/activity/activitypriorityondelete',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get Config date formate
    public function getDateFormate()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/dateformat',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get Config thread limit setting
    public function getThreadLimit()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/admin_ticketview/threadlimit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get Config thread limit setting
    public function getLoginRequired()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/loginrequired',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get Config thread limit setting
    public function getDashboardActivityLimit()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/activity/dashboardactivitylimit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get Config thread limit setting
    public function getConfigTicketCreationPriority()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/priority_on_creation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get Config thread limit setting
    public function getConfigTicketCreationGroup()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/group_on_creation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get Config thread limit setting
    public function getConfigTicketCreationStatus()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/status_on_creation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Media Path
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath();
    }

    /**
     * Get Media Url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    //get Current Website Id
    public function getDefaultWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    //get config helpdesk name value
    public function getConfigHelpdeskName()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/ticketsystemname',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigHelpdeskEmail()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/ticketsystememail',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigDraftsavetime()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/admin_ticketview/draftsavetime',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigLockviewtime()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/admin_ticketview/lockviewtime',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigAllowedextensions()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/allowedextensions',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigNoAllowFiles()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/noofallowedfiles',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigAllowEditor()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/alloweditor',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigSpamstatus()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketstatus/spamstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigNumAllowFile()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketstatus/spamstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigLockExpireTime()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/admin_ticketview/lockexpiretime',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigCloseStatus()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketstatus/closedstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigResolveStatus()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketstatus/solvestatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigPendingStatus()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketstatus/pendingstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigNewStatus()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketstatus/newstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigOpenStatus()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/ticketstatus/openstatus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getUploadFileSize()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/uploadfilesize',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigCustomerDeleteTicket()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/customer_can_delete_ticket',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigCustomerCloseTicket()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/customer_can_close_ticket',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //get config helpdesk name value
    public function getConfigCustomerCanAddCc()
    {
        return $this->scopeConfig->getValue(
            'helpdesk/helpdesk_settings/customer_can_add_cc',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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

    //Check if access of controller
    public function getPermission($action)
    {
        return $this->_authSession->isAllowed($action);
    }

    //check is Admin or customer
    public function isAdmin()
    {
        $isAdmin = false;
        if ($this->_appState->getAreaCode() == Area::AREA_ADMINHTML) {
            $isAdmin = true;
        }
        return $isAdmin;
    }

    /**
     * Send Email
     */

    public function sendMail(
        $template_name,
        $emailTempVariables,
        $senderInfo,
        $receiverInfo
    ) {
    
        $this->temp_id = $this->getTemplateId($template_name);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTempVariables, $senderInfo, $receiverInfo);
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Get Template Id
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Generate Template For Mail
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template =  $this->transportBuilder->setTemplateIdentifier($this->temp_id)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo);
        if (gettype($receiverInfo['email']) == "array") {
            foreach ($receiverInfo['email'] as $receiver) {
                $template->addTo($receiver, $receiverInfo['name']);
            }
            if (isset($receiverInfo['cc']) && count($receiverInfo['cc'])) {
                foreach ($receiverInfo['cc'] as $cc) {
                    $template->addCc($cc);
                }
            }
            if (isset($receiverInfo['bcc']) && count($receiverInfo['bcc'])) {
                $template->addBcc($receiverInfo['bcc']);
                foreach ($receiverInfo['bcc'] as $bcc) {
                    $template->addBcc($bcc);
                }
            }
        } else {
            $template->addTo($receiverInfo['email'], $receiverInfo['name']);
            if (isset($receiverInfo['cc']) && count($receiverInfo['cc'])) {
                foreach ($receiverInfo['cc'] as $cc) {
                    $template->addCc($cc);
                }
            }
            if (isset($receiverInfo['bcc']) && count($receiverInfo['bcc'])) {
                foreach ($receiverInfo['cc'] as $cc) {
                    $template->addBcc($cc);
                }
            }
        }
        
        return $this;
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
}
