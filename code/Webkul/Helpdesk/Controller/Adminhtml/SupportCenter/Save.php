<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\SupportCenter;

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
     * @var \Webkul\Helpdesk\Model\SupportCenterFactory
     */
    protected $_supportCenterFactory;

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
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\SupportCenterFactory $supportCenterFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_supportCenterFactory = $supportCenterFactory;
        $this->_activityRepo = $activityRepo;
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
            $scId = $this->getRequest()->getParam('entity_id');
            if ($this->getRequest()->getPost()) {
                if (is_array($data['cms_id'])) {
                    $data['cms_id'] = implode(",", $data['cms_id']);
                }
                $model = $this->_supportCenterFactory->create();
                $model->setData($data)->setId($scId);
                $model->save();
                if ($scId) {
                    $this->_activityRepo->saveActivity($scId, $model->getName(), "edit", "supportcenter");
                } else {
                    $this->_activityRepo->saveActivity($model->getId(), $model->getName(), "add", "supportcenter");
                }
                $this->messageManager->addSuccess(__("Support Center successfully saved"));
            } else {
                $this->messageManager->addError(__('Unable to find Support Center to save'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__("There are some error to save Support Center"));
            $this->_helpdeskLogger->info($e->getMessage());
        }
        $this->_redirect("*/*/");
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::supportcenter');
    }
}
