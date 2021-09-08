<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\User;

class Validate extends \Magento\User\Controller\Adminhtml\User\Validate
{
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
