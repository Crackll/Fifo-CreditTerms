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

use Webkul\AccordionFaq\Controller\Adminhtml\Faqgroup as FaqgroupController;
use Magento\Framework\Controller\ResultFactory;

class Edit extends FaqgroupController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Webkul\Accordionfaq\Model\Faqgroup $groupModel */
        $id = $this->getRequest()->getParam('id');
        $groupModel = $this->_objectManager
                ->create(\Webkul\AccordionFaq\Model\Faqgroup::class);
        if ($id) {
            $groupModel->load($this->getRequest()->getParam('id'));
        }
        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)
                ->getFormData(true);
        if (!empty($data)) {
            $groupModel->setData($data);
        }
        $this->_objectManager->get(\Magento\Framework\Registry::class)
                ->register('accordionfaq_faqgroup', $groupModel);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
       
        $resultPage->getConfig()->getTitle()->prepend(
            $groupModel->getId() ?  __('Edit FAQ Group') : __('New FAQ Group')
        );       
        
        $resultPage->addBreadcrumb(__('Manage Group'), __('Manage Group'));
        $content = $resultPage->getLayout()->createBlock(\Webkul\AccordionFaq\Block\Adminhtml\Faqgroup\Edit::class);
        $resultPage->addContent($content);
        $left = $resultPage->getLayout()
            ->createBlock(\Webkul\AccordionFaq\Block\Adminhtml\Faqgroup\Edit\Tabs::class);
        $resultPage->addLeft($left);
        return $resultPage;
    }
}
