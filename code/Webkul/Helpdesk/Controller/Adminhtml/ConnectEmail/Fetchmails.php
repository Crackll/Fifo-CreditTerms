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

class Fetchmails extends \Magento\Backend\App\Action
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
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\MailfetchRepository $mailfetchRepo
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_connectEmailFactory = $connectEmailFactory;
        $this->_modelSession = $modelSession;
        $this->_activityRepo = $activityRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_mailfetchRepo = $mailfetchRepo;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try {
            $cEmailId = $this->getRequest()->getParam('id');
            if ($cEmailId) {
                $count = $this->_mailfetchRepo->fetchMail($cEmailId);
                $this->messageManager->addSuccess(__("%1 email was successfully fetched.", $count));
                $this->_redirect("*/*/edit", ["id" => $cEmailId]);
                return;
            } else {
                $this->messageManager->addError(__("Item does not exist"));
                $this->_redirect("*/*/");
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->_redirect("*/*/edit", ["id" => $cEmailId]);
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
