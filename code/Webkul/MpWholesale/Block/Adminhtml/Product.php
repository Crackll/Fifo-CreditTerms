<?php
/**
 * Webkul wholesale unit index layout
 *
 * @category    Webkul
 * @package     Webkul_MpWholesale
 * @author      Webkul Software Private Limited
 *
 */
namespace Webkul\MpWholesale\Block\Adminhtml;

class Product extends \Magento\Backend\Block\Widget\Container
{
    /**
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Webkul\MpWholesale\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $role = $this->authSession->getUser()->getRole();
        if ($role->getRoleName() == 'Wholesaler') {
            $addButtonProps = [
                'id' => 'add_new_product',
                'label' => __('Add New Product'),
                'class' => 'add primary',
                'name' => 'add',
                'onclick' => "setLocation('" . $this->_getProductCreateUrl() . "')"
            ];
            $this->buttonList->add('add_new', $addButtonProps);
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve product create url
     * @return string
     */
    protected function _getProductCreateUrl()
    {
        return $this->getUrl(
            'mpwholesale/*/new'
        );
    }
}
