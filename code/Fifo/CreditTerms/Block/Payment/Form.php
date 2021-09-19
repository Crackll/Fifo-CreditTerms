<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fifo\CreditTerms\Block\Payment;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\AccountManagementInterface;

/**
 * Customer edit form block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Form extends \Magento\Customer\Block\Account\Dashboard
{
    protected $_directoryBlock;
    protected $_buyerCreditTermFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Directory\Block\Data $directoryBlock,
        \Fifo\CreditTerms\Model\CreditTermsFactory $buyerCreditTermFactory,
        array $data = []
    ){
        $this->_directoryBlock = $directoryBlock;
        $this->_buyerCreditTermFactory = $buyerCreditTermFactory;
        parent::__construct($context, $customerSession,$subscriberFactory,$customerRepository,$customerAccountManagement);
    }

    public function getRegionsAction()
    {
        return $this->getUrl('creditterms/payment/regions', ['_secure' => true]);
    }

    /**
     * Retrieve form data
     *
     * @return array
     */
    protected function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->customerSession->getCustomerFormData(true);
            $data = [];
            if ($formData) {
                $data['data'] = $formData;
                $data['customer_data'] = 1;
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Restore entity data from session. Entity and form code must be defined for the form.
     *
     * @param \Magento\Customer\Model\Metadata\Form $form
     * @param null $scope
     * @return \Magento\Customer\Block\Form\Register
     */
    public function restoreSessionData(\Magento\Customer\Model\Metadata\Form $form, $scope = null)
    {
        $formData = $this->getFormData();
        if (isset($formData['customer_data']) && $formData['customer_data']) {
            $request = $form->prepareRequest($formData['data']);
            $data = $form->extractData($request, $scope, false);
            $form->restoreData($data);
        }

        return $this;
    }

    public function getCountryList()
    {
        $country = $this->_directoryBlock->getCountryHtmlSelect();
        return $country;
    }

    public function getRegions()
    {
        $region = $this->_directoryBlock->getRegionHtmlSelect();
        return $region;
    }

    public function getBuyerCreditTerms(){
        $collection = $this->_buyerCreditTermFactory->create()->getCollection()
            ->addFieldToSelect(['terms_name','payment_terms','credit_limit'])
            ->addFieldToFilter("type", \Fifo\CreditTerms\Model\Source\TypeOptions::BUYER_TYPE);

        $termOptions = [];
        foreach ($collection as $termData){
            $termName = $termData->getData('terms_name');
            $paymentTerms = $termData->getData('payment_terms');
            $creditLimit = $termData->getData('credit_limit');
            $termOptions[$termName] = __($termName." Credit Term (Upto ".$paymentTerms." days and ".$creditLimit." credit limit)");
        }

        foreach ($termOptions as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
