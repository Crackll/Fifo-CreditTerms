<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\User;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\MpWholesale\Controller\Adminhtml\User
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Webkul\MpWholesale\Model\WholeSaleUserFactory
     */
    private $wholeSaleUserFactory;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Webkul\MpWholesale\Model\WholeSaleUserFactory $wholeSaleUserFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\MpWholesale\Model\WholeSaleUserFactory $wholeSaleUserFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->wholeSaleUserFactory = $wholeSaleUserFactory;
        $this->userFactory = $userFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $wholeSaleuserId=(int)$this->getRequest()->getParam('id');
        $wholeSaleUsermodel=$this->wholeSaleUserFactory->create();
        if ($wholeSaleuserId) {
            $wholeSaleUsermodel->load($wholeSaleuserId);
            if (!$wholeSaleUsermodel->getEntityId()) {
                $this->messageManager->addError(__('This Wholesaler user no longer exists.'));
                $this->_redirect('mpwholesale/*/');
                return;
            }
        }

        $userId = $wholeSaleUsermodel->getUserId();
        /** @var \Magento\User\Model\User $model */
        $model = $this->userFactory->create();

        if ($userId) {
            $model->load($userId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This user no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        } else {
            $model->setInterfaceLocale(Resolver::DEFAULT_LOCALE);
        }

        // Restore previously entered form data from session
        $data = $this->_session->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('wholesale_user', $model);
        $this->coreRegistry->register('wholesale_userData', $wholeSaleUsermodel);

        if (isset($userId)) {
            $breadcrumb = __('Edit User');
        } else {
            $breadcrumb = __('New User');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Users'));
        $this->_view->getPage()->getConfig()->getTitle()
                                ->prepend($model->getId() ? $model->getName() : __('New User'));
        $this->_view->renderLayout();
    }
    /**
     * check wholesale user save pemission
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
                ->isAllowed('Webkul_MpWholesale::user');
    }
}
