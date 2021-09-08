<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Agentsmanagement\Group;

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
     * @var \Webkul\Helpdesk\Model\GroupFactory
     */
    protected $_groupFactory;

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
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_groupFactory = $groupFactory;
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
            $groupId = isset($data['entity_id'])?$data['entity_id']:0;
            if (empty($data)) {
                $this->messageManager->addError(__('Unable to find group to save'));
                return $this->_redirect('helpdesk/*/');
            }
            if ($groupId) {
                $model = $this->_groupFactory->create()->load($groupId);
                $model->setData($data);
                $model->save();
            } else {
                $model = $this->_groupFactory->create();
                $model->setGroupName($data["group_name"]);
                $model->setBusinesshourId($data["businesshour_id"]);
                $model->setIsActive($data["is_active"]);
                $model->save();
            }
            $this->messageManager->addSuccess(__("Group successfully saved"));
            $this->_redirect("*/*/");
        } catch (\Exception $e) {
            $this->messageManager->addError(__("There are some error to save type"));
            $this->_helpdeskLogger->info($e->getMessage());
            $this->_redirect("*/*/edit", ["entity_id" => $groupId]);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::group');
    }
}
