<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fifo\CreditTerms\Block\Payment;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;

/**
 * Customer edit form block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Form extends \Magento\Customer\Block\Account\Dashboard
{
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
}
