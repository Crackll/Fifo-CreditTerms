<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Customer\Customer;

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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\CustomerFactory $customerFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_customerFactory = $customerFactory;
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
            $helpdeskCustomerId = isset($data['entity_id'])?$data['entity_id']:0;
            if (empty($data)) {
                $this->messageManager->addError(__('Unable to find group to save'));
                return $this->_redirect('helpdesk/*/');
            }
            if ($helpdeskCustomerId) {
                $model = $this->_customerFactory->create()->load($helpdeskCustomerId);
                $model->setData($data);
                $model->save();
                $this->_activityRepository->saveActivity($helpdeskCustomerId, $model->getName(), "edit", "customer");
            } else {
                $customer = $this->_customerFactory->create()->getCollection();
                $customer->addFieldToFilter('email', $data["email"]);
                $customer->addFieldToFilter('customer_id', $data["customer_id"]);
                if (!$customer->getSize()) {
                    $model = $this->_customerFactory->create();
                    $model->setName($data["name"]);
                    $model->setEmail($data["email"]);
                    $model->setCustomerId($data["customer_id"]);
                    $model->setOrganizations($data["organizations"]);
                    $model->save();
                    $this->_activityRepository->saveActivity($model->getId(), $model->getName(), "add", "customer");
                    $this->messageManager->addSuccess(__("Customer successfully saved"));
                } else {
                    $this->messageManager->addError(__("Email is already added with selected customer!!"));
                }
            }
            $this->_redirect("*/*/");
        } catch (\Exception $e) {
            $this->messageManager->addError(__("There are some error to save type"));
            $this->_helpdeskLogger->info($e->getMessage());
            $this->_redirect("*/*/edit", ["id" => $helpdeskCustomerId]);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::customers');
    }
}
