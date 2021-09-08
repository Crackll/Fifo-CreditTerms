<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */

namespace Webkul\WebApplicationFirewall\Block\Adminhtml\System\Config;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Webkul\WebApplicationFirewall\Helper\Data as Helper;

/**
 * WAF DownloadButton
 */
class DownloadButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_WebApplicationFirewall::system/config/download.phtml';
    /**
     * @var Helper
     */
    protected $_helper;
    /**
     * DownloadButton constructor.
     *
     * @param Context $context
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Generate collect button html
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(Button::class)
            ->setData([
                'id'    => 'download_button',
                'label' => $this->isInstalled()? __('Download GeoIP2 Library'): __('Update GeoIP2 Library'),
            ]);
        return $button->toHtml();
    }

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('waf/config/download');
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    public function isInstalled()
    {
        return $this->_helper->checkIpLocationDb() ? '' : 'hidden="hidden';
    }
}
