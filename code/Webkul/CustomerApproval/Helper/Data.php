<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerApproval
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerApproval\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $customerRepository;

    protected $storeManager;

    protected $inlineTranslation;

    protected $transportBuilder;

    protected $escaper;

    protected $urlFactory;

    /**
     * @param Context                                            $context
     * @param CustomerRepositoryInterface                        $customerRepository
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param \Magento\Framework\Escaper                         $escaper
     * @param \Magento\Framework\UrlFactory                             $urlFactory
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlFactory $urlFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->escaper = $escaper;
        $this->urlFactory = $urlFactory;
        parent::__construct($context);
    }

    /**
     * get Customer Approval status function
     *
     * @param  int $customerId
     * @return int
     */
    public function getCustomerApprovalStatus($customerId)
    {
        $isApproved = '0';
        $customer = $this->customerRepository->getById($customerId);
        $attrib = $customer->getCustomAttributes();
        if (isset($attrib['wk_customer_approval'])) {
            $isApproved = $attrib['wk_customer_approval']->getValue();
        }
        return $isApproved;
    }

    /**
     *   get config value
     *
     * @return boolean
     */
    public function isAutoApproval()
    {
        $isAuto = $this->scopeConfig->getValue(
            'customerapproval/general_settings/auto_approval',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        return $isAuto;
    }

    /**
     * afterLoginMessage function
     *
     * @return string
     */
    public function afterLoginMessage()
    {
        $msg = $this->scopeConfig->getValue(
            'customerapproval/general_settings/not_approved_afterlogin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->escaper->escapeHtml($msg);
    }

    /**
     * afterRegistrationMessage function
     *
     * @return string
     */
    public function afterRegistrationMessage()
    {
        $msg = $this->scopeConfig->getValue(
            'customerapproval/general_settings/not_approved_afterregistration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->escaper->escapeHtml($msg);
    }

    /**
     * sendmail after customer register
     *
     * @param  [customer repository] $customer
     * @return void
     */
    public function afterRegisterMail($customer)
    {
        $template_id = $this->scopeConfig->getValue(
            'customerapproval/email/customer_registration_after_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $templateVars = [
            'myvar1' => $customer->getFirstName() ? $customer->getFirstName() : $customer->getName(),
            'myvar2' => __('Registration Successful.')
        ];
        
        $this->sendMail($template_id, $templateVars, $customer);
    }

    /**
     * sendapproval mail function
     *
     * @param  [customer Repository] $customer
     * @return void
     */
    public function sendApprovalMail($customer)
    {
        $template_id = $this->scopeConfig->getValue(
            'customerapproval/email/customer_approval_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $storeId = $customer->getStoreId();
        $url = $this->urlFactory->create();//->create();
        $url->setScope($storeId);
        $params['_nosid'] = true;

        $templateVars = [
            'myvar1' => $customer->getFirstName() ? $customer->getFirstName() : $customer->getName(),
            'myvar2' => __('Account Approved.'),
            'myvar3' => $url->getBaseUrl('', $params).'customer/account/login'
        ];
       
        $this->sendMail($template_id, $templateVars, $customer);
    }

    /**
     * send Disapproval mail function
     *
     * @param  $customer
     * @return void
     */
    public function sendDisapprovalMail($customer)
    {
        $template_id = $this->scopeConfig->getValue(
            'customerapproval/email/customer_disapproval_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        $templateVars = [
            'myvar1' => $customer->getFirstName() ? $customer->getFirstName() : $customer->getName(),
            'myvar2' => __('Account Disapproved.')
        ];
        $this->sendMail($template_id, $templateVars, $customer);
    }

    /**
     * Mail sending function
     *
     * @param  [string]              $templateId
     * @param  [array]               $templateVars
     * @param  [customer repository] $customer
     * @return void
     */
    public function sendMail($templateId, $templateVars, $customer)
    {
        try {
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $customer->getStoreId()
            ];

            $adminEmail = $this->recieverEmail();
            $adminName = $this->recieverName();
            $from = ['email' => $adminEmail, 'name' => $adminName];

             /* In Magento 2.3.2 */
            //$to = [$customer->getEmail()];

            /* In Magento 2.3.3 */
            $to = $customer->getEmail();
        
            $this->inlineTranslation->suspend();
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Mail to admin after customer registration
     *
     * @param $customer
     * @return void
     */
    public function afterRegisterMailToAdmin($customer)
    {
        $template_id = $this->scopeConfig->getValue(
            'customerapproval/email/admin_notification_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $templateVars = [
            'myvar1' => $customer->getEmail(),
            'myvar2' => __('Customer Approval Required')
        ];
        
        $this->sendMailToAdmin($template_id, $templateVars, $customer);
    }

    /**
     * Mail sending function to Admin
     *
     * @param  [string]              $templateId
     * @param  [array]               $templateVars
     * @param  [customer repository] $customer
     * @return void
     */
    public function sendMailToAdmin($templateId, $templateVars, $customer)
    {
        try {
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ];

            $adminEmail = $this->recieverEmail();
            $adminName = $this->recieverName();
            
            $from = ['email' => $adminEmail, 'name' => $adminName];
            
            $to = $adminEmail;
            $this->inlineTranslation->suspend();
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
                $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Get Email function of Admin
     * @return [string]
     */
    public function recieverEmail()
    {
        $adminEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $email = $this->scopeConfig->getValue(
            'customerapproval/admin_notificatin/admin_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($email == null) {
            return $adminEmail;
        } else {
            return $email;
        }
    }

    /**
     * Get Name function of Admin
     * @return [string]
     */
    public function recieverName()
    {
        $adminName = $this->scopeConfig->getValue(
            'customerapproval/admin_notificatin/admin_name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $name = 'Admin';

        if ($adminName == null) {
            return $name;
        } else {
            return $adminName;
        }
    }

    /**
     * Get Confirmation email status
     *
     * @param string $websiteId
     * @return void
     */
    public function getConfirmationRequired($websiteId = '')
    {
        $cnf = $this->scopeConfig->getValue(
            'customer/create_account/confirm',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        return $cnf;
    }
}
