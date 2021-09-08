<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\User;

use Webkul\MpWholesale\Model\WholeSaleUserFactory;

class Delete extends \Webkul\MpWholesale\Controller\Adminhtml\User
{

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var WholeSaleUserFactory
     */
    private $wholeSaleUserFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context,
     * @param \Magento\User\Model\UserFactory $userFactory,
     * @param WholeSaleUserFactory $wholeSaleUserFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\User\Model\UserFactory $userFactory,
        WholeSaleUserFactory $wholeSaleUserFactory
    ) {
        parent::__construct($context);
        $this->userFactory = $userFactory;
        $this->wholeSaleUserFactory = $wholeSaleUserFactory;
    }
    /**
     * @return void
     */
    public function execute()
    {
        $userId = (int)$this->getRequest()->getParam('id');
        if ($userId) {
            try {
                $wholesaleUser = $this->wholeSaleUserFactory->create();
                $entityId = $wholesaleUser->load($userId)->getUserId();
                /** @var \Magento\User\Model\User $model */
                $model = $this->userFactory->create();
                $model->setId($entityId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the wholesale user.'));
                $this->_redirect('mpwholesale/user/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('mpwholesale/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a user to delete.'));
        $this->_redirect('mpwholesale/user/');
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
