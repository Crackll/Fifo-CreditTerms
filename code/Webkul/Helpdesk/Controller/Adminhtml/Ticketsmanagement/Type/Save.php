<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Ticketsmanagement\Type;

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
     * @var \Webkul\Helpdesk\Model\TypeFactory
     */
    protected $_typeFactory;

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
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepo
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_typeFactory = $typeFactory;
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
            $typeId = $this->getRequest()->getParam('entity_id');
            if ($this->getRequest()->getPost()) {
                $model = $this->_typeFactory->create();
                $model->setData($data)->setId($typeId);
                $model->save();
                if ($typeId) {
                    $this->_activityRepo->saveActivity($typeId, $model->getName(), "edit", "tickettype");
                } else {
                    $this->_activityRepo->saveActivity($model->getId(), $model->getName(), "add", "tickettype");
                }
                $this->messageManager->addSuccess(__("Type successfully saved"));
            } else {
                $this->messageManager->addError(__('Unable to find ticket type to save'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__("There are some error to save type"));
            $this->_helpdeskLogger->info($e->getMessage());
        }
        $this->_redirect("*/*/");
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::type');
    }
}
