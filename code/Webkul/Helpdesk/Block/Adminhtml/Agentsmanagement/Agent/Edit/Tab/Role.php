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
namespace Webkul\Helpdesk\Block\Adminhtml\Agentsmanagement\Agent\Edit\Tab;

/**
 * Adminhtml Helpdesk Ticket Status Edit Form
 */
class Role extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Webkul\Accordionfaq\Model\AddfaqFactory $faqFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_userRolesFactory = $userRolesFactory;
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('agent_tabs');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_userRolesFactory->create();
        $collection->setRolesFilter();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'assigned_user_role',
            [
                'header_css_class'  =>  'a-center',
                'header'            =>  __('Assigned'),
                'type'              =>  'radio',
                'html_name'         =>  'roles[]',
                'align'             =>  'center',
                'filter'            =>      false,
                'index'             =>  'role_id',
                'values' => $this->getSelectedRoles()
            ]
        );
        $this->addColumn(
            'role_name',
            [
                'header'            =>  __('Role Name'),
                'index'             => 'role_name'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @param bool $json
     * @return array|string
     */
    public function getSelectedRoles($json = false)
    {
        /* @var $user \Magento\User\Model\User */
        $user = $this->_coreRegistry->registry('permissions_agent');
        //checking if we have this data and we
        //don't need load it through resource model
        if ($user->hasData('roles')) {
            $userRoles = $user->getData('roles');
        } else {
            $userRoles = $user->getRoles();
        }

        if ($json) {
            $jsonRoles = [];
            foreach ($userRoles as $roleId) {
                $jsonRoles[$roleId] = 0;
            }
            return $this->_jsonEncoder->encode((object)$jsonRoles);
        } else {
            return $userRoles;
        }
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $userPermissions = $this->_coreRegistry->registry('permissions_agent');
        return $this->getUrl('*/*/grid', ['user_id' => $userPermissions->getUserId()]);
    }
}
