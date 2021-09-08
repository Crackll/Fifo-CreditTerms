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
namespace Webkul\MpWholesale\Controller\Adminhtml\Product;

class NewAction extends \Webkul\MpWholesale\Controller\Adminhtml\Product
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
