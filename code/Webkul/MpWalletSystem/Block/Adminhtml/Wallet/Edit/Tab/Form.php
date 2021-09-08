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

namespace Webkul\MpWalletSystem\Block\Adminhtml\Wallet\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    protected $_template = 'tab/walletamount.phtml';
    protected $blockGrid;

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Wallet Amount');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Wallet Amount');
    }
    
    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }
    
    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }
    
    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }
    
    /**
     * Can show tab in tabs.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
    
    /**
     * Tab is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Create form of edit tab
     *
     * @return $this
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_wallet');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Adjust amount to Wallet')]
        );
        $fieldset->addField(
            'walletamount',
            'text',
            [
                'label' => __('Enter amount'),
                'name' => 'walletamount',
            ]
        );
        $fieldset->addField(
            'walletactiontype',
            'select',
            [
                'label' => __('Action want to perform on amount'),
                'title' => __('Action want to perform on amount'),
                'name' => 'walletactiontype',
                'options' => ['credit' => __('Credit Amount'), 'debit' => __('Debit Amount')]
            ]
        );
        $fieldset->addField(
            'walletnote',
            'text',
            [
                'label' => __('Note for the transaction'),
                'name' => 'walletnote',
            ]
        );
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        return $this;
    }

    /**
     * To Html function
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    /**
     * Get Block Grid function
     *
     * @return object
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Webkul\MpWalletSystem\Block\Adminhtml\Wallet\Edit\Tab\Grid::class,
                'walletcustomergrid'
            );
        }
        return $this->blockGrid;
    }
    
    /**
     * Return HTML of grid block.
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }
}
