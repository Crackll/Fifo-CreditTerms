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
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Ui\Component\MassAction\Filter;

class MassEnable extends \Magento\Backend\App\Action
{
    protected $_filter;
    protected $_faqgroup;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        Action\Context $context,
        \Webkul\AccordionFaq\Model\FaqgroupFactory $faqgroupFactory
    ) {
        $this->_filter = $filter;
        $this->_faqgroup = $faqgroupFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_AccordionFaq::faqgroup');
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $faqModel = $this->_faqgroup->create();
        $collection = $this->_filter->getCollection($faqModel->getCollection());
        foreach ($collection as $faq) {
            $faq->setStatus(1);
            $faq->save();
        }
        $this->messageManager->addSuccess(__('FAQ Group(s) enabled successfully.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
