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
use \Magento\Framework\Json\Helper\Data as JsonHelper;

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
    protected $_objectManager;
    protected $_jsonHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Directory\Block\Data $directoryBlock,
        \Fifo\CreditTerms\Model\CreditTermsFactory $buyerCreditTermFactory,
        JsonHelper $jsonHelper,
        array $data = []
    ){
        $this->_directoryBlock = $directoryBlock;
        $this->_jsonHelper = $jsonHelper;
        $this->_buyerCreditTermFactory = $buyerCreditTermFactory;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::__construct($context, $customerSession,$subscriberFactory,$customerRepository,$customerAccountManagement);
    }

    public function getRegionsAction()
    {
        return $this->getUrl('creditterms/payment/regions', ['_secure' => true]);
    }

    public function getSubmitFormAction()
    {
        return $this->getUrl('creditterms/payment/requestcreditpost', ['_secure' => true]);
    }

    public function getCreditApplicationCollection(){
        $collection = $this->_objectManager->create(\Fifo\CreditTerms\Model\CreditTermsApplications::class)
            ->getCollection()
            ->addFieldToSelect(['application_status','created_at','credit_term_category','credit_term_days','credit_term_limit'])
            ->addFieldToFilter('email',$this->customerSession->getCustomer()->getEmail())
            ->getLastItem()->getData();

        $result = [];
        if(count($collection)){
            $result['status'] = \Fifo\CreditTerms\Model\Source\ApplicationStatus::getOptionValueById($collection['application_status']);
            $result['term_category'] = $collection['credit_term_category'];
            $result['term_days'] = $collection['credit_term_days'];
            $result['term_limit'] = $collection['credit_term_limit'];
            $result['date'] = $collection['created_at'];
        }

        return $result;
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
        /** @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory */
        $countryCollectionFactory = $this->_objectManager->get(\Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class);

        /** @var \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection */
        $countryCollection = $countryCollectionFactory->create();
        $countryCollection = $countryCollection->toOptionArray();

        $result = [];
        foreach ($countryCollection as $country){
            if(isset($country['value']) && empty($country['value'])){
                $result[] = [
                    'value'=> '',
                    'label'=> 'Please Select Country'
                ];
            }else{
                $result[] = $country;
            }
        }
        return array_column($result, 'label', 'value');
    }

    public function getAllRegions()
    {
        $collection = $this->_objectManager->create(\Magento\Directory\Model\Region::class)
            ->getCollection();
        $result = [];
        foreach ($collection as $item) {
            $result[$item->getRegionId()] = $item->getName();
        }

        return $result;
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

            $optionData = [
                'termCategory' => $termName,
                'termDays' => $paymentTerms,
                'termLimit' => $creditLimit
            ];
            $optionJson = $this->_jsonHelper->jsonEncode($optionData);

            $termOptions[$optionJson] = __($termName." Credit Term (Upto ".$paymentTerms." days and ".$creditLimit." credit limit)");
        }

        $result = [];
        foreach ($termOptions as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
