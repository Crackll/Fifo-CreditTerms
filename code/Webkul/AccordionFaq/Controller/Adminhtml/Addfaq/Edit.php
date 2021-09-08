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

use Webkul\AccordionFaq\Controller\Adminhtml\Addfaq as AddfaqController;
use Magento\Framework\Controller\ResultFactory;

class Edit extends AddfaqController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if (!$id) {
            $resultPage->getConfig()->getTitle()->prepend(
                __('New FAQ')
            );
        } else {
            $resultPage->getConfig()->getTitle()->prepend(
                __('Edit FAQ')
            );
        }
        
        return $resultPage;
    }
}
