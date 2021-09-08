<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

abstract class Templates extends Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization
                    ->isAllowed(
                        'Webkul_MpPushNotification::Templates'
                    );
    }
}
