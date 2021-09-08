<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Agentsmanagement\Level;

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
     * @var \Webkul\Helpdesk\Model\AgentLevelFactory
     */
    protected $_agentLevelFactory;

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
        \Webkul\Helpdesk\Model\AgentLevelFactory $agentLevelFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_agentLevelFactory = $agentLevelFactory;
        $this->_activityRepository = $activityRepository;
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
            $levelId = isset($data['id'])?$data['id']:0;
            if (empty($data)) {
                $this->messageManager->addError(__('Unable to find level to save'));
                return $this->_redirect('helpdesk/*/');
            }
            if ($levelId) {
                $model = $this->_agentLevelFactory->create()->load($levelId);
                $model->setData($data);
                $model->save();
                $this->_activityRepository->saveActivity($levelId, $model->getName(), "edit", "agentlevel");
            } else {
                $model = $this->_agentLevelFactory->create();
                $model->setName($data["name"]);
                $model->setDescription($data["description"]);
                $model->setStatus($data["status"]);
                $model->save();
                $this->_activityRepository->saveActivity($model->getId(), $model->getName(), "add", "agentlevel");
            }
            $this->messageManager->addSuccess(__("Level successfully saved"));
            $this->_redirect("*/*/");
        } catch (\Exception $e) {
            $this->messageManager->addError(__("There are some error to save type"));
            $this->_helpdeskLogger->info($e->getMessage());
            $this->_redirect("*/*/edit", ["id" => $levelId]);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::level');
    }
}
