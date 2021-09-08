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
namespace Webkul\MpWholesale\Controller\Adminhtml\Leads;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class SendEmail extends Action
{
    /**
     * @var \Webkul\MpWholesale\Helper\Email
     **/
    protected $mailHelper;
    /**
     * @var \Webkul\MpWholesale\Model\LeadsFactory
     */
    protected $leadsFactory;

    /**
     * @param Context $context
     * @param \Webkul\MpWholesale\Helper\Email $mailHelper
     * @param \Webkul\MpWholesale\Model\LeadsFactory $leadsFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     **/
    public function __construct(
        Context $context,
        \Webkul\MpWholesale\Helper\Email $mailHelper,
        \Webkul\MpWholesale\Model\LeadsFactory $leadsFactory,
        \Magento\Customer\Model\CustomerFactory $customerModel
    ) {
        parent::__construct($context);
        $this->mailHelper = $mailHelper;
        $this->leadsFactory = $leadsFactory;
        $this->customerModel = $customerModel;
    }

    /**
     * send mail to csutomer
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            $leadId = 0;
            if ($data['mailBody'] == "") {
                $message = __('Please check the details entered');
                $this->messageManager->addError($message);
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
            $leadId = $data['lead_id'];
        } catch (\Exception $e) {
            $message = __($e->getMessage());
            $this->messageManager->addError($message);
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $leadDetails = $this->leadsFactory->create()->load($leadId);
        $customerDetails = $this->customerModel->create()->load($leadDetails->getSellerId());
        $data['seller_email'] = $customerDetails->getEmail();
        $data['seller_name'] = $customerDetails->getFirstname().' '.$customerDetails->getLastname();
        try {
            $this->mailHelper->sendCustomMail(
                $data
            );
            $this->messageManager->addSuccess(__('Mail Sent Successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Error Sending Mail'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpWholesale::leads');
    }
}
