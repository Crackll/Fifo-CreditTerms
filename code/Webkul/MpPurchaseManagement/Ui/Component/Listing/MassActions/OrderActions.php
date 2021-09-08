<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPurchaseManagement\Ui\Component\Listing\MassActions;

use Magento\Ui\Component\MassAction;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Backend\Model\Auth\Session;

class OrderActions extends MassAction
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param ContextInterface $context
     * @param Session $authSession
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        Session $authSession,
        array $components,
        array $data
    ) {
        parent::__construct($context, $components, $data);
        $this->authSession = $authSession;
    }
    
    /**
     * @inheritDoc
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->authSession->getUser()->getRole()->getRoleName() == 'Wholesaler') {
            $this->setData('config', '');
        }
    }
}
