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

namespace Webkul\AccordionFaq\Controller\Adminhtml\Addfaq;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @param Action\Context                                   $context
     * @param \Webkul\AccordionFaq\Model\AddfaqFactory         $addfaqFactory
     */
    public function __construct(
        Action\Context $context,
        \Webkul\AccordionFaq\Model\AddfaqFactory $addfaqFactory
    ) {
        $this->_faq = $addfaqFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_AccordionFaq::addfaq');
    }

    /**
     * Save action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $time = date('Y-m-d H:i:s');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        if ($data) {
            $data['faq'] = strip_tags($data['faq']);
            $data['body'] = str_replace(['<script>','</script>'], '', $data['body']);
            $id = (int) $this->getRequest()->getParam('id');
            $model = $this->_faq->create();
            $data['updated_time'] = $time;
            if ($id) {
                $model->addData($data)->setId($id)->save();
            } else {
                unset($data['id']);
                $data['created_time'] = $time;
                $model->setData($data)->save();
            }
        }
        $this->messageManager->addSuccess(__('FAQ saved successfully.'));

        return $resultRedirect->setPath('*/*/');
    }
}
