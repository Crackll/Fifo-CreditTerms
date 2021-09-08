<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Agentsmanagement\Agent;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class Save extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        parent::__construct($context, $coreRegistry, $userFactory);
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_agentFactory = $agentFactory;
        $this->_userFactory = $userFactory;
        $this->_activityRepo = $activityRepo;
        $this->_authSession = $authSession;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();
            $userId = isset($data['user_id'])?$data['user_id']:0;
            $agentId = isset($data['entity_id'])?$data['entity_id']:0;
            if (empty($data)) {
                $this->messageManager->addError(__('Unable to find agent to save'));
                return $this->_redirect('helpdesk/*/');
            }
            $model = $this->_userFactory->create();

            if ($userId) {
                $model->load($userId);
            }
            
            $model->setData($this->_getAdminUserData($data));

            $userRoles = $this->getRequest()->getParam('roles', []);
            
            if (count($userRoles)) {
                $model->setRoleId($userRoles[0]);
            }

            /** @var $currentUser \Magento\User\Model\User */
            $currentUser = $this->_authSession->getUser();
            /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
            $currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;

            $isCurrentUserPasswordValid = isset($data[$currentUserPasswordField])
            && !empty($data[$currentUserPasswordField]) && is_string($data[$currentUserPasswordField]);
            
            if (!$isCurrentUserPasswordValid) {
                
                throw new AuthenticationException(__('You have entered an invalid password for current user.'));
            }
            
            $currentUser->performIdentityCheck($data[$currentUserPasswordField]);
            $model->save();            
            $data['user_id'] = $model->getId();
            if ($agentId) {
               
                $agent = $this->_agentFactory->create()->load($agentId);
                $agent->setTicketScope($data['ticket_scope']);
                $agent->setTimezone($data['timezone']);
                $agent->setSignature($data['signature']);
                $agent->setLevel($data['level']);
                $agent->setGroupId($data['group_id']);
                $agent->save();
            } else {
                
                $agent = $this->_agentFactory->create();
                $agent->setUserId($data["user_id"]);
                $agent->setTicketScope($data["ticket_scope"]);
                $agent->setTimezone($data["timezone"]);
                $agent->setLevel($data["level"]);
                $agent->setSignature($data["signature"]);
                $agent->setGroupId($data["group_id"]);
                $agent->save();
            }
            
            if ($agentId) {
                $this->_activityRepo->saveActivity($agentId, $model->getName(), "edit", "agent");
            } else {                
                $this->_activityRepo->saveActivity($agent->getId(), $model->getName(), "add", "agent");
            }
            $this->messageManager->addSuccess(__("Agent has been successfully saved"));
            $this->_getSession()->setUserData(false);
            $this->_redirect("*/*/");
        } catch (\Exception $e) {
            
            $this->messageManager->addError(__($e->getMessage()));
            $this->_helpdeskLogger->info($e->getMessage());
            $this->_redirect("*/*/edit", ["user_id" => $model->getId()]);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::agent');
    }
}
