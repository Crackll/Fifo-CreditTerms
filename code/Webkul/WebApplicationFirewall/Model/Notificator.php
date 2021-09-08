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

declare(strict_types=1);

namespace Webkul\WebApplicationFirewall\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\DeploymentConfig;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Area;
use Magento\Email\Model\BackendTemplate;
use Magento\User\Model\NotificatorException;
use Magento\User\Api\Data\UserInterface;
use Webkul\WebApplicationFirewall\Api\Data\SecurityLoginInterface;
use Magento\Framework\DataObject;

/**
 * @inheritDoc
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Notificator
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DeploymentConfig
     */
    private $deployConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param TransportBuilder $transportBuilder
     * @param ConfigInterface $config
     * @param DeploymentConfig $deployConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigInterface $config,
        DeploymentConfig $deployConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->deployConfig = $deployConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Send a notification.
     *
     * @param string $templateConfigId
     * @param array $templateVars
     * @param string $toEmail
     * @param string $toName
     * @throws MailException
     *
     * @return void
     */
    private function sendNotification(
        string $templateConfigId,
        array $templateVars,
        string $toEmail,
        string $toName,
        string $area = Area::AREA_ADMINHTML
    ): void {
        
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->config->getValue($templateConfigId))
            ->setTemplateModel(BackendTemplate::class)
            ->setTemplateOptions([
                'area' => $area,
                'store' => Store::DEFAULT_STORE_ID
            ])
            ->setTemplateVars($templateVars)
            ->setFrom(
                $this->config->getValue('waf_setting/emails/notification_sender')
            )
            ->addTo($toEmail, $toName)
            ->getTransport();
        $transport->sendMessage();
    }
    /**
     * Send Login notification to admin user.
     *
     * @param AdminLoginInterface $loginUser
     * @return void
     * @throws NotificatorException
     */
    public function sendAdminLogin(AdminLoginInterface $loginUser, UserInterface $user): void
    {
        try {
            $this->sendNotification(
                'waf_setting/emails/admin_login_alert',
                [
                    'loginUser' => $loginUser,
                    'user' => $user,
                    'store' => $this->storeManager->getStore(
                        Store::DEFAULT_STORE_ID
                    )
                ],
                $loginUser->getEmail(),
                $loginUser->getName()
            );
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Send reset password link to all sub admin users.
     *
     * @param UserInterface $user
     * @return void
     * @throws NotificatorException
     */
    public function sendPasswordReset(UserInterface $user)
    {
        try {
            $this->sendNotification(
                'waf_setting/emails/password_reset',
                [
                    'user' => $user,
                    'store' => $this->storeManager->getStore(
                        Store::DEFAULT_STORE_ID
                    )
                ],
                $user->getEmail(),
                $user->getFirstName().' '.$user->getLastName(),
                Area::AREA_FRONTEND
            );
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Send admin login failed notification
     *
     * @param SecurityLoginInterface[] $securityLogin
     * @param array $toEmails
     * @return void
     * @throws LocalizedException
     */
    public function sendLoginFailed(array $securityLogin, $toEmails = [])
    {
        try {
            foreach ($toEmails as $toEmail) {
                $this->sendNotification(
                    'waf_setting/emails/login_failed',
                    [
                        'loginLogs' => $securityLogin,
                        'store' => $this->storeManager->getStore(
                            Store::DEFAULT_STORE_ID
                        )
                    ],
                    $toEmail,
                    '',
                    Area::AREA_FRONTEND
                );
            }
            
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Send Admin user locked notification
     *
     * @param UserInterface $user
     * @param array $loginLogs
     * @param array $toEmails
     * @return void
     */
    public function sendUserLocked(UserInterface $user, array $loginLogs, array $toEmails)
    {
        try {
            foreach ($toEmails as $toEmail) {
                $this->sendNotification(
                    'waf_setting/emails/user_locked',
                    [
                        'user' => $user,
                        'loginLogs' => $loginLogs,
                        'store' => $this->storeManager->getStore(
                            Store::DEFAULT_STORE_ID
                        )
                    ],
                    $toEmail,
                    '',
                    Area::AREA_FRONTEND
                );
            }
            
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Send file upload notification
     *
     * @param UserInterface $user
     * @param array $loginLogs
     * @param array $toEmails
     * @return void
     */
    public function sendFileUploadNotification(DataObject $fileData, string $toEmail)
    {
        try {
            $this->sendNotification(
                'waf_setting/emails/file_uploade_alert',
                [
                    'fileData' => $fileData,
                    'store' => $this->storeManager->getStore(
                        Store::DEFAULT_STORE_ID
                    )
                ],
                $toEmail,
                '',
                Area::AREA_FRONTEND
            );
            
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Send file upload notification
     *
     * @param UserInterface $user
     * @param array $loginLogs
     * @param array $toEmails
     * @return void
     */
    public function sendMalaciousFileUploadNotification(DataObject $fileData, string $toEmail)
    {
        try {
            $this->sendNotification(
                'waf_setting/emails/malacious_uploade_alert',
                [
                    'fileData' => $fileData,
                    'store' => $this->storeManager->getStore(
                        Store::DEFAULT_STORE_ID
                    )
                ],
                $toEmail,
                '',
                Area::AREA_FRONTEND
            );
            
        } catch (LocalizedException $exception) {
            throw new NotificatorException(
                __($exception->getMessage()),
                $exception
            );
        }
    }
}
