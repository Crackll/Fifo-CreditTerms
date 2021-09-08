<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml\Events\Edit\Tab;

class ActionTemplate extends \Webkul\Helpdesk\Block\Adminhtml\Edit\Tab\AbstractAction
{
    const TEMPLATE = 'Webkul_Helpdesk::events/edit/tab/action.phtml';
    
    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::TEMPLATE);
        }
        return $this;
    }
}
