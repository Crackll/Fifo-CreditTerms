<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml\Emailtemplate;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

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
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Tickets Edit Block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Webkul_Helpdesk';
        $this->_controller = 'adminhtml_emailtemplate';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_Helpdesk::emailtemplate')) {
            $this->buttonList->update('save', 'label', __('Save'));
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded image
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('New Email Template');
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
    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            };
        ";
        $template = $this->getEmailTemplate();
        if ($template->getId()) {
            $this->buttonList->add(
                'delete',
                [
                    'label' => __('Delete'),
                    'onclick' => "window.location.href = '" . $this->getDeleteUrl() . "'",
                    'class' => 'delete-template'
                ]
            );
        }

        $this->buttonList->add(
            'preview',
            [
                'label' => __('Preview Template'),
                'data_attribute' => [
                    'role' => 'template-preview',
                ]
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Get paths of where current template is currently used
     *
     * @param bool $asJSON
     * @return string
     */
    public function getCurrentlyUsedForPaths($asJSON = true)
    {
        /** @var $template \Magento\Email\Model\BackendTemplate */
        $template = $this->getEmailTemplate();
        $paths = $template->getSystemConfigPathsWhereCurrentlyUsed();
        $pathsParts = $this->_getSystemConfigPathsParts($paths);
        if ($asJSON) {
            return $this->_jsonEncoder->encode($pathsParts);
        }
        return $pathsParts;
    }

    //get Current Email Template
    public function getEmailTemplate()
    {
        return $this->_coreRegistry->registry('current_email_template');
    }

    /**
     * Convert xml config paths to decorated names
     *
     * @param array $paths
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getSystemConfigPathsParts($paths)
    {
        $result = $urlParams = $prefixParts = [];
        $scopeLabel = __('Default Config');
        return $result;
    }

    public function getDeleteUrl()
    {
        $id = (int)$this->getEmailTemplate()->getId();
        return $this->getUrl('*/*/delete', ['id'=>$id, '_current' => true]);
    }

    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/preview');
    }

    /**
     * Return true if template type is text; return false otherwise
     *
     * @return bool
     */
    public function isTextType()
    {
        return $this->getEmailTemplate()->isPlain();
    }

    /**
     * Return template type from template object
     *
     * @return int
     */
    public function getTemplateType()
    {
        return $this->getEmailTemplate()->getType();
    }
}
