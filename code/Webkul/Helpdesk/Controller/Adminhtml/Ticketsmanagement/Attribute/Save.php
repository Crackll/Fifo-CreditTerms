<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Ticketsmanagement\Attribute;

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
        \Webkul\Helpdesk\Model\Eav\CustomAttributeFactory $eavCustomAttrFactory,
        \Webkul\Helpdesk\Model\ActivityRepository $activityRepository,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Eav\Model\Entity\Attribute $eavEntityAttr,
        \Webkul\Helpdesk\Model\TicketsCustomAttributesFactory $ticketCustomAttrFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_eavCustomAttrFactory = $eavCustomAttrFactory;
        $this->_activityRepository = $activityRepository;
        $this->_eavEntity = $eavEntity;
        $this->_eavEntityAttr = $eavEntityAttr;
        $this->_ticketCustomAttrFactory = $ticketCustomAttrFactory;
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
            $attrId = isset($data['attribute_id'])?$data['attribute_id']:0;
            if (empty($data)) {
                $this->messageManager->addError(__('Unable to find attribute to save'));
                return $this->_redirect('helpdesk/*/');
            }
            if ($attrId) {
                $model = $this->_eavCustomAttrFactory->create()->load($attrId);
                $model->setData($data);
                $model->save();
                $this->_activityRepository->saveActivity(
                    $attrId,
                    $model->getFrontendLabel(),
                    "edit",
                    "ticketcustomfield"
                );
            } else {
                $model = $this->_eavCustomAttrFactory->create();
                $model->setData($data);
                $model->save();
                $this->_activityRepository->saveActivity(
                    $model->getId(),
                    $model->getFrontendLabel(),
                    "add",
                    "ticketcustomfield"
                );
            }
            $id = $model->getId();
            $data['entity_type_id'] = $this->_eavEntity->setType('ticketsystem_ticket')->getTypeId();
            if (isset($data['entity_type_id']) && isset($data['attribute_code']) && $data['attribute_code']) {
                $attribute = $this->_eavEntityAttr->loadByCode('ticketsystem_ticket', $data['attribute_code']);
                if ($data['frontend_input'] == 'boolean') {
                    $attribute->setData('source_model', \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class);
                }
                $attribute->save();
                $ticketAttribute = $this->_ticketCustomAttrFactory->create()->getCollection()
                                    ->addFieldToFilter('attribute_id', ['eq'=>$attribute->getAttributeId()])
                                    ->getFirstItem();
                if (!$ticketAttribute->getIndexId()) {
                    $ticketAttributeTemp = $this->_ticketCustomAttrFactory->create();
                    $ticketAttributeTemp->setShowInFront(0);
                    $ticketAttributeTemp->setAttributeId($attribute->getId());
                    $ticketAttributeTemp->setFieldDependency($data['field_dependency']);
                    $ticketAttributeTemp->setStatus($data['is_visible']);
                    $ticketAttributeTemp->save();
                }
            } else {
                $ticketAttributeCollection = $this->_ticketCustomAttrFactory->create()->getCollection()
                                    ->addFieldToFilter('attribute_id', ['eq'=>$attrId]);
                foreach ($ticketAttributeCollection as $ticketAttribute) {
                    $ticketAttribute->setFieldDependency($data['field_dependency']);
                    $ticketAttribute->setStatus($data['is_visible']);
                    $ticketAttribute->save();
                }
            }
            $this->messageManager->addSuccess(__("Custom attribute successfully saved"));
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
        return $this->_authorization->isAllowed('Webkul_Helpdesk::customattribute');
    }
}
