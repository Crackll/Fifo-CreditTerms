<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */

namespace Webkul\WebApplicationFirewall\Model;

use Magento\Framework\DataObject;
use Webkul\WebApplicationFirewall\Api\ResetSubuserPasswordInterface;
use Webkul\WebApplicationFirewall\Model\Notificator;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\Backend\Helper\Data;

/**
 * @method int getUserId()
 * @method int getAclRole()
 * @method \Webkul\WebApplicationFirewall\Model\ResetSubuserPassword setAclRole($aclRole)
 */
class ResetSubuserPassword extends DataObject implements ResetSubuserPasswordInterface
{

    /**
     * Notificator
     *
     * @var Notificator
     */
    protected $notificator;

    /**
     * @var CollectionFactory
     */
    protected $userCollectionFactory;

    /**
     * @var Data
     */
    protected $backendDataHelper;

    /** @var \Magento\User\Api\Data\UserInterfaceFactory */
    protected $userFactory;
    
    /**
     * @param \Magento\User\Api\Data\UserInterfaceFactory $userFactory
     * @param Notificator $notificator
     * @param CollectionFactory $userCollectionFactory
     * @param Data $backendDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\User\Api\Data\UserInterfaceFactory $userFactory,
        Notificator $notificator,
        CollectionFactory $userCollectionFactory,
        Data $backendDataHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->notificator = $notificator;
        $this->_userFactory = $userFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->backendDataHelper = $backendDataHelper;
    }

    /**
     * @inheritDoc
     */
    public function setUser($user)
    {
        $this->setAclRole((int) $user->getAclRole());
        $this->addData($user->getData());
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendResetRequest()
    {
        if ($this->getAclRole() === 1) {
            $collection = $this->userCollectionFactory->create();
            $collection->addFieldToFilter('is_active', 1);
            $collection->load(false);
            try {
                if ($collection->getSize() > 0) {
                    foreach ($collection as $item) {
                        /** @var \Magento\User\Model\User $user */
                        $user = $this->_userFactory->create()->load($item->getId());
                        if ($user->getId() && ($user->getId() !== $this->getUserId())) {
                            $this->invalidatePasswords($user, rand());
                            $this->notificator->sendPasswordReset($user);
                        }
                    }
                }
            } catch (\Exception $exception) {
                throw $exception;
            }
            
        }
    }

    /**
     * Save user with reset password
     *
     * @param \Magento\User\Api\Data\UserInterface $object
     * @param string $data
     * @return $this
     */
    private function invalidatePasswords($object, $password)
    {
        $resource = $this->userCollectionFactory->create()->getResource();
        if ($object->getId()) {
            $resource->getConnection()->update(
                $resource->getMainTable(),
                ['password' => $password],
                ['user_id = ?' => (int)$object->getId()]
            );
        }

        return $this;
    }
}
