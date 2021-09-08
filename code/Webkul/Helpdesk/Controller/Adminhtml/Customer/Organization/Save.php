<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Customer\Organization;

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
     * @var \Webkul\Helpdesk\Model\CustomerOrganizationFactory
     */
    protected $_customerOrgFactory;

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
        \Webkul\Helpdesk\Model\CustomerOrganizationFactory $customerOrgFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_customerOrgFactory = $customerOrgFactory;
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
            $orgId = isset($data['entity_id'])?$data['entity_id']:0;
            if (empty($data)) {
                $this->messageManager->addError(__('Unable to find organization to save'));
                return $this->_redirect('helpdesk/*/');
            }
            if (array_key_exists("customers", $data)) {
                $data["customers"] = implode(",", $data["customers"]);
            }
            if (array_key_exists("groups", $data)) {
                 $data["groups"] = implode(",", $data["groups"]);
            }
            if ($orgId) {
                $model = $this->_customerOrgFactory->create()->load($orgId);
                $model->setData($data);
                $model->save();
                $this->_activityRepository->saveActivity($orgId, $model->getName(), "edit", "organization");
            } else {
                $model = $this->_customerOrgFactory->create();
                $model->setName($data["name"]);
                $model->setDescription($data["description"]);
                $model->setDomain($data["domain"]);
                $model->setNotes($data["notes"]);
                $model->setCustomers(isset($data["customers"])?$data["customers"]:'');
                $model->setCustomerRole($data["customer_role"]);
                $model->setGroups(isset($data["groups"])?$data["groups"]:'');
            
                $model->save();
                $this->_activityRepository->saveActivity($model->getId(), $model->getName(), "add", "customer");
            }
            $this->messageManager->addSuccess(__("Organization successfully saved"));
            $this->_redirect("*/*/");
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->_helpdeskLogger->info($e->getMessage());
            $this->_redirect("*/*/edit", ["id" => $orgId]);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::customerorganization');
    }
}
