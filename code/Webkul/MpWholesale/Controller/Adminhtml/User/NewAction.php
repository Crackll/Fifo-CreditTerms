<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\User;

class NewAction extends \Webkul\MpWholesale\Controller\Adminhtml\User
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
    /**
     * check wholesale user save pemission
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
                ->isAllowed('Webkul_MpWholesale::user');
    }
}
