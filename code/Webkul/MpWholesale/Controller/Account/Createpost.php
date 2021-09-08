<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Authorization\Model\RoleFactory;
use Webkul\MpWholesale\Model\WholeSaleUserFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Action\Context;
use Webkul\MpWholesale\Helper\Email as MpWholesaleEmail;

class Createpost extends Action
{
  /**
   * @var MpWholesaleEmail
   */
    private $mpWholesaleEmail;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    private $helperData;

  /**
   * @param Context $context
   * @param RoleFactory $roleFactory
   * @param FormKeyValidator $formKeyValidator
   * @param \Magento\User\Model\UserFactory $userFactory
   * @param MpWholesaleEmail $mpWholesaleEmail
   * @param \Webkul\MpWholesale\Helper\Data $helperData
   * @param WholeSaleUserFactory $wholeSaleUserFactory
   */
    public function __construct(
        Context $context,
        RoleFactory $roleFactory,
        FormKeyValidator $formKeyValidator,
        \Magento\User\Model\UserFactory $userFactory,
        MpWholesaleEmail $mpWholesaleEmail,
        \Webkul\MpWholesale\Helper\Data $helperData,
        WholeSaleUserFactory $wholeSaleUserFactory
    ) {
        parent::__construct($context);
        $this->userFactory = $userFactory;
        $this->roleFactory = $roleFactory;
        $this->wholeSaleUserFactory = $wholeSaleUserFactory;
        $this->mpWholesaleEmail = $mpWholesaleEmail;
        $this->helperData = $helperData;
        $this->formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/create',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            $data = $this->getRequest()->getParams();
            if (isset($data['wholesaler_conf']) && $data['wholesaler_conf'] == 1) {
                $data['is_active'] = 1;
                if ($this->helperData->isWholesalerApprovalRequired()) {
                    $data['is_active'] = 0;
                }
                $model = $this->userFactory->create();
                $model->setData($this->_getUserData($data));
                $roleId=(int)$this->roleFactory->create()->getCollection()->addFieldToFilter('role_name', 'Wholesaler')
                                        ->setPageSize(1)->getFirstItem()->getRoleId();
                if ($roleId) {
                    $model->setRoleId($roleId);
                }
                try {
                    $model->save();
                    $this->messageManager->addSuccess(__('Wholesale user account has been created successfully.'));
                    $data['user_id']=$model->getUserId();
                    $wholeSaleUserData = $this->getDataForWholeSaleUser($data);
                    if (isset($data['is_active']) && $data['is_active'] == 0) {
                        $this->messageManager->addNotice(__("Please wait for the admin approval."));
                        $this->mpWholesaleEmail->sendBecomeWholesaleNotification($wholeSaleUserData);
                    }
                    $this->mpWholesaleEmail->sendWholesalerCreateNotification($wholeSaleUserData);
                    $this->messageManager->addNotice(
                        __('Please check your email %1 for the login details', $data['email'])
                    );
                    $this->_redirect('*/*/create');
                } catch (\Magento\Framework\Validator\Exception $e) {
                    $messages = $e->getMessages();
                    $this->messageManager->addMessages($messages);
                    $this->_redirect('*/*/create');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if ($e->getMessage()) {
                        $this->messageManager->addError($e->getMessage());
                    }
                    $this->_redirect('*/*/create');
                }
            } else {
                $this->messageManager->addError("Please accept the Privacy Policy !!");
            }
        }
        $this->_redirect('*/*/create');
    }

  /**
   * Retrieve well-formed admin user data from the form input
   *
   * @param array $data
   * @return array
   */
    protected function _getUserData(array $data)
    {
        if (isset($data['password']) && $data['password'] === '') {
            unset($data['password']);
        }
        if (!isset($data['password']) && isset($data['password_confirmation'])
          && $data['password_confirmation'] === '') {
            unset($data['password_confirmation']);
        }
        return $data;
    }

  /**
   * getDataForWholeSaleUser
   * @param array $data
   * @return void
   */
    private function getDataForWholeSaleUser($data)
    {
        $data['status'] = 1;
        if ($this->helperData->isWholesalerApprovalRequired()) {
            $data['status'] = 0;
        }
        $wholeSaleUsermodel = $this->wholeSaleUserFactory->create();
        $wholeSaleUsermodel->setData($data);
        $wholeSaleUsermodel->save();
        return $data;
    }
}
