<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\EmailTemplate;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Email\Model\Template;

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
     * @var \Webkul\Helpdesk\Model\EmailTemplateFactory
     */
    protected $_emailtemplateFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_modelSession;

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
        \Webkul\Helpdesk\Model\EmailTemplateFactory $emailtemplateFactory,
        \Magento\Backend\Model\Session $modelSession,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Magento\Email\Model\BackendTemplate $emailbackendTemp,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_authSession = $authSession;
        $this->_emailtemplateFactory = $emailtemplateFactory;
        $this->_modelSession = $modelSession;
        $this->_activityRepo = $activityRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_emailbackendTemp = $emailbackendTemp;
        $this->date = $date;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try {
            $id = (int)$this->getRequest()->getParam('entity_id');
            $data = $this->getRequest()->getParams();
            $template = $this->_emailbackendTemp->load($id);
            if (!$template->getId() && $id) {
                $this->messageManager->addError(__('This Email template no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            $template->setTemplateSubject($data['template_subject'])
                ->setTemplateCode($data['template_code'])
                ->setTemplateText($data['template_text'])
                ->setTemplateStyles($data['template_styles'])
                ->setModifiedAt($this->date->gmtDate())
                ->setOrigTemplateVariables($data['orig_template_variables']);

            if (!$template->getId()) {
                $template->setAddedAt($this->date->gmtDate());
                $template->setTemplateType(Template::TYPE_HTML);
            }

            if (isset($data['_change_type_flag'])) {
                $template->setTemplateType(Template::TYPE_TEXT);
                $template->setTemplateStyles('');
            }
            $template->save();
            if ($this->getRequest()->getParam("id")) {
                $this->_activityRepo->saveActivity(
                    $this->getRequest()->getParam("id"),
                    $template->getTemplateCode(),
                    "edit",
                    "email"
                );
            } else {
                $this->_activityRepo->saveActivity($template->getId(), $template->getTemplateCode(), "add", "email");
            }
            $ticketTemplate = $this->_emailtemplateFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter("template_id", ["eq"=>$template->getId()]);
            if (!count($ticketTemplate)) {
                $ticketTemplate = $this->_emailtemplateFactory->create();
                $ticketTemplate->setTemplateId($template->getId());
                $ticketTemplate->save();
            }

            $this->_modelSession->setFormData(false);
            $this->messageManager->addSuccess(__('The email template has been saved.'));
            $this->_redirect('*/*');
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->_helpdeskLogger->info($e->getMessage());
            $this->_modelSession->setFormData($data);
            $this->_redirect('*/*');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::events');
    }
}
