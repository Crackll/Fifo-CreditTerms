<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Block\Adminhtml\Lead;

class Details extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    /*
     * changes the default buttons at the layout
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Webkul_MpWholesale';
        $this->_controller = 'adminhtml_lead_details';

        parent::_construct();
        $this->buttonList->remove('save');
        $this->buttonList->add(
            'my_back',
            [
                'label' =>  'Back',
                'onclick'   => 'setLocation(\'' . $this->getUrl('mpwholesale/leads/index') . '\')',
                'class'     =>  'back'
            ],
            100
        );
        $this->buttonList->add('leadsMail', [
            'label'   => __('Send Mail'),
            'class'   => 'primary'
        ]);

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get Lead If
     *
     * @return int
     */
    public function getLeadId()
    {
        return $leadId = $this->getRequest()->getParam('id');
    }

    /**
     * Get WholeSaler Full Name
     *
     * @return string
     */
    public function getName()
    {
        $user =  $this->authSession->getUser();
        $name = $user->getFirstname().' '.$user->getLastname();
        return $name;
    }

    /**
     * Get WholeSaler Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->authSession->getUser()->getEmail();
    }
}
