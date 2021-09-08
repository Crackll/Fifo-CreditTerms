<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Block\Adminhtml\System\Config\Form;
 
use Magento\Framework\App\Config\ScopeConfigInterface;
 
class Button extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $mpHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MpAdvertisementManager\Helper\Data $mpHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mpHelper=$mpHelper;
    }

    const BUTTON_TEMPLATE = 'Webkul_MpAdvertisementManager::system/config/form/button.phtml';

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
    
    public function getMpHelper()
    {
        return $this->mpHelper;
    }
    
    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    /**
     * Return list addons ajax url
     *
     * @return string
     */
    public function getAjaxSaveUrl()
    {
        return $this->getUrl('mpadvertisementmanager/pricing/ads');
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
        
        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData(
                [
                    'id'        => 'ads_config',
                    'label'     => __('Configure Ads'),
                    'class' => 'primary'
                ]
            );
        return $button->_toHtml();
    }
}
