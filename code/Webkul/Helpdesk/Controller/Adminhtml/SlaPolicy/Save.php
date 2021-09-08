<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\SlaPolicy;

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
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @var \Webkul\Helpdesk\Model\SlapolicyFactory
     */
    protected $_slapolicyFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_modelSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Webkul\Helpdesk\Model\ActivityRepository
     */
    protected $_activityRepo;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Model\SlapolicyFactory $slapolicyFactory,
        \Magento\Backend\Model\Session $modelSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_authSession = $authSession;
        $this->_slapolicyFactory = $slapolicyFactory;
        $this->_modelSession = $modelSession;
        $this->_jsonHelper = $jsonHelper;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->serializer = $serializer;
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
            $slaId = isset($data['id'])?$data['id']:0;
            if (!array_key_exists('one_condition', $data) && !array_key_exists('all_condition', $data)) {
                $this->_redirect($this->_redirect->getRefererUrl());
                $this->messageManager->addError(__("Please select condition for SLA!"));
                return;
            }
            if (!$data) {
                $this->_redirect('helpdesk/*/');
                $this->messageManager->addError(__('Unable to find events to save'));
                return;
            }
            
            $data['one_condition_check'] = $this->_jsonHelper->jsonEncode(isset($data
            ['one_condition'])?$data['one_condition']:null);
            $data['all_condition_check'] = $this->_jsonHelper->jsonEncode(isset($data
            ['all_condition'])?$data['all_condition']:null);
            $data['sla_service_level_targets'] = $this->serializer->serialize($data['sla']);
            if ($slaId) {
                $model = $this->_slapolicyFactory->create()->load($slaId);
                $model->setData($data);
                $model->save();
            } else {
                $model = $this->_slapolicyFactory->create();
                $model->setData($data);
                $model->save();
            }
            $this->messageManager->addSuccess(__("SLA Policy successfully saved"));
            $this->_modelSession->setFormData(false);
        } catch (\Exception $e) {
            $this->messageManager->addError(__("There are some error to save event"));
            $this->_helpdeskLogger->info($e->getMessage());
            $this->_modelSession->setFormData($data);
        }
        $this->_redirect("*/*/");
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::slapolicy');
    }
}
