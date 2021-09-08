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

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Ui\Component\MassAction\Filter;

class Delete extends \Magento\Backend\App\Action
{
    protected $_faq;

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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id=$this->getRequest()->getParam('id');
        if ($id) {
            $faq = $this->_faq->create()->load($id);
            $faq->delete();
            $this->messageManager->addSuccess(__('FAQ(s) deleted successfully.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
