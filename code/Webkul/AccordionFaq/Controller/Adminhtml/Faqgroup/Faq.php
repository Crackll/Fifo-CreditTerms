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

class Faq extends \Webkul\AccordionFaq\Controller\Adminhtml\Faqgroup
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    protected $faqgroup;

    protected $session;

    protected $registry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Webkul\AccordionFaq\Model\FaqgroupFactory $faqgroup
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Webkul\AccordionFaq\Model\FaqgroupFactory $faqgroup,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->faqgroup = $faqgroup;
        $this->session = $session;
        $this->registry = $registry;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $galleryModel = $this->faqgroup->create();
        if ($this->getRequest()->getParam('id')) {
            $galleryModel->load($this->getRequest()->getParam('id'));
        }
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $galleryModel->setData($data);
        }
        $this->registry->register('accordionfaq_faqgroup', $galleryModel);
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('accordionfaq.faqgroup.edit.tab.faq');
        return $resultLayout;
    }
}
