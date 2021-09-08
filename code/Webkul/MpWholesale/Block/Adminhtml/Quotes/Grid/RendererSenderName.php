<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Block\Adminhtml\Quotes\Grid;

use Webkul\MpWholesale\Helper\Data;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class RendererSenderName extends AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];
    /**
     * @var Webkul\MpWholesale\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Data                           $helper
     * @param \Magento\Framework\Escaper     $escaper
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Data $helper,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->helper       =   $helper;
        $this->escaper      =   $escaper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->_actions = [];
        $actions = [];
        if ($row->getMsgFrom() != "seller") {
            $actions[] = __('Me');
        } else {
            $senderData = $this->helper->getCustomerData(
                $row->getSenderId()
            );
            $actions[] = $senderData->getName();
        }
        $this->addToActions($actions);
        return $this->_actionsToHtml();
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();

        if (empty($actions)) {
            $actions = $this->_actions;
        }
        foreach ($actions[0] as $action) {
            $html[] = '<span>' . $action . '</span>';
        }
        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
