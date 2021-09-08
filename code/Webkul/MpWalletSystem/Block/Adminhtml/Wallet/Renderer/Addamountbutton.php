<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Block\Adminhtml\Wallet\Renderer;

/**
 * Webkul MpWalletSystem Block
 */
class Addamountbutton extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Escaper     $escaper
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->escaper = $escaper;
    }

    /**
     * Array to store all options data.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Return Actions.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->actions = [];
        $actions[0] = [
            '@' => [
                'type' => 'button',
                'class' => 'button wk_addamount',
                'customer-id' => $row->getEntityId(),
                'customer-name' => preg_replace('#<script(.*?)>(.*?)</script>#is', '', $row->getName()),
                'title' => __('Adjust amount'),
            ],
            '#' => __('Adjust amount'),
        ];
        $this->addToActions($actions);

        return $this->_actionsToHtml();
    }

    /**
     * Get escaped value.
     *
     * @param string $value
     *
     * @return string
     */
    protected function _getEscapedValue($value)
    {
        // phpcs:ignore
        return addcslashes($this->escaper->escapeHtml($value), '\\\'');
    }

    /**
     * Render options array as a HTML string.
     *
     * @param array $actions
     *
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();
        if (empty($actions)) {
            $actions = $this->actions;
        }
        foreach ($actions[0] as $action) {
            if (!empty($action['@'])) {
                $attributesObject->setData($action['@']);
                $html[] = '<button '.$attributesObject->serialize().'>'.$action['#'].'</button>';
            } else {
                $html[] = '<span>'.$action['#'].'</span>';
            }
        }

        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage.
     *
     * @param array $actionArray
     */
    public function addToActions($actionArray)
    {
        $this->actions[] = $actionArray;
    }
}
