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

abstract class Quotation extends \Magento\Backend\App\AbstractAction
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
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpWholesale::quotation');
    }
}
