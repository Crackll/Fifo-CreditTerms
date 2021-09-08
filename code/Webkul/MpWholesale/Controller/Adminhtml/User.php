<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleFac;

abstract class User extends \Magento\Backend\App\AbstractAction
{
    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Webkul_MpWholesale::menu');
        return $this;
    }

    /**
     * Retrieve well-formed admin user data from the form input
     *
     * @param array $data
     * @return array
     */
    protected function _getAdminUserData(array $data)
    {
        if (isset($data['password']) && $data['password'] === '') {
            unset($data['password']);
        }
        if (!isset($data['password']) && isset($data['password_confirmation'])
            && $data['password_confirmation'] === '') {
            unset($data['password_confirmation']);
        }
        return $data;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_User::acl_users');
    }
}
