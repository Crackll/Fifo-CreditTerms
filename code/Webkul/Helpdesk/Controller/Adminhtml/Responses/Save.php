<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Responses;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\Helpdesk\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_modelSession;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\ResponsesFactory $responsesFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepository,
        \Magento\Backend\Model\Session $modelSession,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_responsesFactory = $responsesFactory;
        $this->_activityRepo = $activityRepository;
        $this->_modelSession = $modelSession;
        $this->_authSession = $authSession;
        $this->_jsonHelper = $jsonHelper;
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
            $responseId = isset($data['entity_id'])?$data['entity_id']:0;
            if (!$data) {
                $this->_redirect('helpdesk/*/');
                $this->messageManager->addError(__('Unable to find response to save'));
                return;
            }
            $data["agent_id"] = $this->_authSession->getUser()->getId();
            if (isset($data['action'])) {
                $data['actions'] = $this->_jsonHelper->jsonEncode($data['action']);
            }

            if (isset($data['groups'])) {
                $data['groups'] = implode(',', $data['groups']);
            }
            if ($responseId) {
                $model = $this->_responsesFactory->create()->load($responseId);
                $model->setData($data);
                $model->save();
                $this->_activityRepo->saveActivity($responseId, $model->getName(), "edit", "response");
            } else {
                $model = $this->_responsesFactory->create();
                $model->setName($data["name"]);
                $model->setDescription($data["description"]);
                $model->setAgentId($data["agent_id"]);
                $model->setCanUse($data["can_use"]);
                isset($data["actions"])?$model->setActions($data["actions"]):$model->setActions("");
                $model->setStatus($data["status"]);
                $model->save();
                $this->_activityRepo->saveActivity($model->getId(), $model->getName(), "add", "response");
            }
            $this->messageManager->addSuccess(__("Response successfully saved"));
            $this->_modelSession->setFormData(false);
            $this->_redirect("*/*/");
        } catch (\Exception $e) {
            $this->messageManager->addError(__("There are some error to save type"));
            $this->_modelSession->setFormData($data);
            $this->_helpdeskLogger->info($e->getMessage());
        }
        $this->_redirect("*/*/");
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::responses');
    }
}
