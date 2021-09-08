<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Block\Adminhtml\User;

/**
 * Wholesale user edit page
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

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

        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_user';
        $this->_blockGroup = 'Webkul_MpWholesale';
        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save User'));
        $this->buttonList->update('delete', 'label', __('Delete User'));
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('permissions_user')->getId()) {
            $username = $this->escapeHtml(
                $this->coreRegistry->registry('permissions_user')->getUsername()
            );
            return __("Edit Wholesale User '%1'", $username);
        } else {
            return __('New Wholesale User');
        }
    }

    /**
     * Return validation url for edit form
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('mpwholesale/*/validate', ['_current' => true]);
    }
}
