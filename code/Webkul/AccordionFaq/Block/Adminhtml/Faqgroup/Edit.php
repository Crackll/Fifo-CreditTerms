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
namespace Webkul\AccordionFaq\Block\Adminhtml\Faqgroup;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize imagegallery gallery edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'faqgroup_id';
        $this->_blockGroup = 'Webkul_AccordionFaq';
        $this->_controller = 'adminhtml_faqgroup';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_AccordionFaq::faqgroup')) {
            $this->buttonList->update('save', 'label', __('Save FAQ Group'));
            $this->buttonList->remove('reset');
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded gallery
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('accordionfaq_faqgroup')->getId()) {
            $title = $this->_coreRegistry->registry('accordionfaq_faqgroup')->getTitle();
            $title = $this->escapeHtml($title);
            return __("Edit Group '%'", $title);
        } else {
            return __('New Group');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
