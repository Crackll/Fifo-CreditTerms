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

namespace Webkul\WebApplicationFirewall\Plugin\Magento\User\Model\ResourceModel;

use Webkul\WebApplicationFirewall\Helper\Data;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Webkul\WebApplicationFirewall\Model\SecurityLogin;
use Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterfaceFactory;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * UpdateFailure Plugin class
 */
class User
{
    /** @var Data  */
    protected $helper;

    protected $loginLogs = [];

    public function __construct(
        \Webkul\WebApplicationFirewall\Model\Notificator $notificator,
        Request $request,
        Data $helper,
        SecurityLoginInterfaceFactory $securityLoginFactory,
        Iterator $iterator,
        TimezoneInterface $timezone
    ) {
        $this->notificator = $notificator;
        $this->_request = $request;
        $this->helper = $helper;
        $this->securityLoginFactory = $securityLoginFactory;
        $this->iterator = $iterator;
        $this->timezone = $timezone;
    }

    /**
     * UpdateFailure plugin function
     *
     * @param \Magento\User\Model\ResourceModel\User $subject
     * @param \Magento\User\Model\User $user
     * @param string $setLockExpires
     * @param string $setFirstFailure
     * @return void
     */
    public function beforeUpdateFailure(
        \Magento\User\Model\ResourceModel\User $subject,
        $user,
        $setLockExpires,
        $setFirstFailure
    ) {
        $isEnabled = $this->helper->getConfigData('brute_force', 'enable');
        if ($isEnabled && false !== $setLockExpires) {
            $firstFailureTime = (new \DateTime($user->getFirstFailure()))->getTimestamp();

            $collection = $this->securityLoginFactory->create()->getCollection();
            $collection->addFieldToFilter(
                'login_status',
                ['eq' => SecurityLogin::LOGIN_FAILED]
            )->addFieldToFilter('time', ['gteq' => $firstFailureTime])
            ->addFieldToFilter('username', ['eq' => $user->getUsername()])
            ->addFieldToFilter('is_sent_mail', ['eq' => 0])
            ->setOrder('time', 'DESC');

            $this->iterator->walk(
                $collection->getSelect(),
                [[$this, 'getSecurityLoginModel']]
            );

            $this->_sendNotification($user);
        }
    }

    /**
     * Assign model objects to array
     *
     * @param array $args
     * @return void
     */
    public function getSecurityLoginModel($args)
    {
        $model = $this->securityLoginFactory->create();
        $time = $args['row']['time'];

        $args['row']['time'] = $this->timezone->date(
            $args['row']['time']
        )->format('M d Y H:i:s');

        $model->setData($args['row']);
        $this->loginLogs[] = $model;

        $model->setTime($time);
        $model->setIsSentMail(1);
        $model->getResource()->save($model);
    }

    /**
     * Send account lock email.
     *
     * @param \Magento\User\Model\User $user
     * @return void
     */
    protected function _sendNotification(\Magento\User\Model\User $user)
    {
        $sendToEmails = explode(',', $this->helper->getConfigData('brute_force', 'admin_emails'));
        array_push($sendToEmails, $user->getEmail());
        $sendToEmails = array_map('trim', $sendToEmails);
        
        $this->notificator->sendUserLocked(
            $user,
            $this->loginLogs,
            $sendToEmails
        );
    }
}
