<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ElasticSearch
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ElasticSearch\Block\Adminhtml\System\Config;
 
use Magento\Framework\App\Config\ScopeConfigInterface;
 
class Reset extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'Webkul_ElasticSearch::system/config/reset.phtml';

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }
    
    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    /**
     * Return list addons ajax url
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('elastic/server/reset');
    }
    
    /**
     * Get the button and scripts contents
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * getButtonHtml get button html
     *
     * @return html
     */
    public function getButtonHtml()
    {
        
        $button = $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'id'        => 'elastic_connection_reset',
                    'label'     => __('Reset Connection'),
                    'class' => 'primary'
                ]
            );
        return $button->_toHtml();
    }
}
