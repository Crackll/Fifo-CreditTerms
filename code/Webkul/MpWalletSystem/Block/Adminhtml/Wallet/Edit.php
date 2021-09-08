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

namespace Webkul\MpWalletSystem\Block\Adminhtml\Wallet;

/**
 * Webkul MpWalletSystem Block
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize walletsystem edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Webkul_MpWalletSystem';
        $this->_controller = 'adminhtml_wallet';

        parent::_construct();
        $this->buttonList->remove('save');
        $this->buttonList->add(
            'my_back',
            [
                'label' =>  'Back',
                'onclick'   => 'setLocation(\'' . $this->getUrl('mpwalletsystem/wallet/index') . '\')',
                'class'     =>  'back'
            ],
            100
        );
        $this->buttonList->remove('back');
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Adjust Amount');
    }

    /**
     * Check permission for passed action
     *
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
