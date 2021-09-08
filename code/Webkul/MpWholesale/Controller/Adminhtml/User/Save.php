<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\User;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Authorization\Model\RoleFactory;
use Magento\Framework\Validator\Locale as ValidatorLocale;
use Magento\Backend\Model\Locale\Manager as LocaleManager;
use Webkul\MpWholesale\Model\WholeSaleUserFactory;
use Webkul\MpWholesale\Helper\Email as MpWholesaleEmail;

class Save extends \Webkul\MpWholesale\Controller\Adminhtml\User
{
    /**
     * @var \Magento\Backend\Model\Locale\Manager
     */
    private $localeManager;

    /**
     * @var \Magento\Authorization\Model\RoleFactory
     */
    private $roleFactory;

    /**
     * @var \Magento\Framework\Validator\Locale
     */
    private $validatorLocale;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \Magento\User\Model\User
     */
    private $currentUser;

    /**
     * @var \Webkul\MpWholesale\Model\WholeSaleUserFactory
     */
    private $wholeSaleUserFactory;

    /**
     * @var MpWholesaleEmail
     */
    private $mpWholesaleEmail;

    /**
     * @param \Magento\Backend\App\Action\Context $context,
     * @param LocaleManager $localeManager,
     * @param ValidatorLocale $validatorLocale,
     * @param RoleFactory $roleFactory,
     * @param \Magento\User\Model\UserFactory $userFactory,
     * @param MpWholesaleEmail $mpWholesaleEmail,
     * @param WholeSaleUserFactory $wholeSaleUserFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        LocaleManager $localeManager,
        ValidatorLocale $validatorLocale,
        RoleFactory $roleFactory,
        \Magento\User\Model\UserFactory $userFactory,
        MpWholesaleEmail $mpWholesaleEmail,
        WholeSaleUserFactory $wholeSaleUserFactory
    ) {
        parent::__construct($context);
        $this->localeManager = $localeManager;
        $this->validatorLocale = $validatorLocale;
        $this->userFactory = $userFactory;
        $this->roleFactory = $roleFactory;
        $this->currentUser = $context->getAuth()->getUser();
        $this->mpWholesaleEmail = $mpWholesaleEmail;
        $this->wholeSaleUserFactory = $wholeSaleUserFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('mpwholesale/*/');
            return;
        }
        /** @var $model \Magento\User\Model\User */
        $model = $this->userFactory->create()->load($userId);
        if ($userId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This user no longer exists.'));
            $this->_redirect('mpwholesale/*/');
            return;
        }
        $model->setData($this->_getAdminUserData($data));
        $roleId=(int)$this->roleFactory->create()->getCollection()->addFieldToFilter('role_name', 'Wholesaler')
                                        ->setPageSize(1)->getFirstItem()->getRoleId();
        if ($roleId) {
            $model->setRoleId($roleId);
        }
        /** Before updating wholeSaleUser user data, ensure that password of current user is entered and is correct */
        $currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
        $isCuntAdminUserPwdValid = isset($data[$currentUserPasswordField])
            && !empty($data[$currentUserPasswordField]) && is_string($data[$currentUserPasswordField]);
        try {
            if (!($isCuntAdminUserPwdValid && $this->currentUser->verifyIdentity($data[$currentUserPasswordField]))) {
                throw new AuthenticationException(__('You have entered an invalid password for current user.'));
            }
            $model->save();
            $this->messageManager->addSuccess(__('You saved the wholesale user.'));

            $data['user_id']=$model->getUserId();
            $wholeSaleUserData = $this->getDataForWholeSaleUser($data);

            if (!$userId) {
                $this->mpWholesaleEmail->sendWholesalerCreateNotification($wholeSaleUserData);
            }
            $this->_getSession()->setUserData(false);
            $this->_redirect('mpwholesale/*/');
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->_redirect('mpwholesale/*/');
        }
    }

    /**
     * getDataForWholeSaleUser
     * @param array $data
     * @return void
     */
    private function getDataForWholeSaleUser($data)
    {
        $data['status'] = 1;
        $wholeSaleUserId = (int)$this->getRequest()->getParam('entity_id');
        $wholeSaleUsermodel = $this->wholeSaleUserFactory->create();
        if ($wholeSaleUserId) {
            $wholeSaleUsermodel->load($wholeSaleUserId);
            $wholeSaleUsermodel->setId($wholeSaleUsermodel);
            $data['id'] = $wholeSaleUserId;

            if ($wholeSaleUsermodel->isObjectNew()) {
                $this->messageManager->addError(__('This wholeSaleUser no longer exists.'));
                $this->_redirect('mpwholesale/*/');
                return;
            }
        }
        $wholeSaleUsermodel->setData($data);
        $wholeSaleUsermodel->save();
        return $data;
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    private function redirectToEdit(array $data)
    {
        $this->_getSession()->setUserData($data);
        $data['entity_id']=isset($data['entity_id'])?$data['entity_id']:0;
        $arguments = $data['entity_id'] ? ['id' => $data['entity_id']]: [];
        $arguments = array_merge(
            $arguments,
            ['_current' => true, 'active_tab' => '']
        );
        if ($data['entity_id']) {
            $this->_redirect('mpwholesale/*/edit', $arguments);
        } else {
            $this->_redirect('mpwholesale/*/index');
        }
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
