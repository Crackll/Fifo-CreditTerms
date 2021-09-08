<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\ConnectEmail;

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
        \Webkul\Helpdesk\Model\ConnectEmailFactory $connectEmailFactory,
        \Magento\Backend\Model\Session $modelSession,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_connectEmailFactory = $connectEmailFactory;
        $this->_modelSession = $modelSession;
        $this->_activityRepo = $activityRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
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
            $cEmailId = $this->getRequest()->getParam('id');
            if ($this->getRequest()->getPost()) {
                $cEmailModel = $this->_connectEmailFactory->create();
                if (empty($data['count'])) {
                    $data['count'] = $data['fetch_email_limit'];
                }
                $cEmailModel->setData($data)->setId($cEmailId);
                $cEmailModel->save();
                if ($cEmailId) {
                    $this->_activityRepo->saveActivity($cEmailId, $cEmailModel->getName(), "edit", "connectemail");
                } else {
                    $this->_activityRepo->saveActivity(
                        $cEmailModel->getId(),
                        $cEmailModel->getName(),
                        "add",
                        "connectemail"
                    );
                }
                $this->messageManager->addSuccess(__("Item was successfully saved"));
                $this->_modelSession->setFormData(false);
            }
            $this->_redirect("*/*/");
        } catch (\Exception $e) {
            $this->messageManager->addError(__("There are some error to save event"));
            $this->_helpdeskLogger->info($e->getMessage());
            $this->_modelSession->setFormData($data);
            $this->_redirect("*/*/");
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::connectemail');
    }
}
