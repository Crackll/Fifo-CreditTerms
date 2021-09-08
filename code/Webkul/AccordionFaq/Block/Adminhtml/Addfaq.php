<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AccordionFaq
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AccordionFaq\Block\Adminhtml;
 
use Magento\Backend\Block\Widget\Grid\Container;
 
class Addfaq extends Container
{
   /**
    * Constructor
    *
    * @return void
    */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_addfaq';
        $this->_blockGroup = 'Webkul_AccordionFaq';
        $this->_headerText = __('Manage FAQ');
        $this->_addButtonLabel = __('Add FAQ');
        parent::_construct();
    }
}
