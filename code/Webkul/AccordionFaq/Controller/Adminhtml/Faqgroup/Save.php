<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AccordionFaq
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AccordionFaq\Controller\Adminhtml\Faqgroup;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    protected $faqgroup;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\AccordionFaq\Model\FaqgroupFactory $faqgroup
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\AccordionFaq\Model\FaqgroupFactory $faqgroup
    ) {
        parent::__construct($context);
        $this->faqgroup = $faqgroup;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_AccordionFaq::faqgroup');
    }

    /**
     * Save action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $flag = false;
        $reserveId = 0;
        $time = date('Y-m-d H:i:s');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        $data['group_code'] = str_replace(" ", "_", $data['group_code']);
        $data['group_code'] = strip_tags($data['group_code']);
        $data['group_name'] = strip_tags($data['group_name']);
        
        $id = (int) $this->getRequest()->getParam('id');
        $groupModel = $this->faqgroup->create();
        $collection = $groupModel->getCollection();
        $collection->addFieldToFilter('group_code', $data['group_code']);
        foreach ($collection as $item) {
            if ($item->getId()) {
                $flag = true;
                $reserveId = $item->getId();
                break;
            }
        }
        if (!empty($data)) {
            $model = $this->faqgroup->create();
            $data['updated_time'] = $time;
            if ($id) {
                if ($id != $reserveId) {
                    if ($flag) {
                        $this->messageManager->addError(
                            __('Group Code "'.$data['group_code'].'" already Exist.')
                        );
                        return $resultRedirect->setPath('*/*/edit', ['id' => $id, '_current' => true]);
                    }
                }
                $model->addData($data)->setId($id)->save();
            } else {
                unset($data['id']);
                if ($flag) {
                    $this->messageManager->addError(
                        __('Group Code "'.$data['group_code'].'" already Exist.')
                    );

                    return $resultRedirect->setPath('*/*/');
                }
                $data['created_time'] = $time;
                $model->setData($data)->save()->getId();
            }
        }
        $this->messageManager->addSuccess(__('Group saved successfully.'));

        return $resultRedirect->setPath('*/*/');
    }
}
