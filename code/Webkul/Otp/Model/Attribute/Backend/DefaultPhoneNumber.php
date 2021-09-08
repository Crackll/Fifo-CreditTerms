<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Otp\Model\Attribute\Backend;

use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

/**
 * Validate phone number for customer registration.
 */
class DefaultPhoneNumber extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @param CustomerHelper $customerHelper
     * @param OtpHelper $otpHelper
     */
    public function __construct(
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper
    ) {
        $this->otpHelper = $otpHelper;
        $this->customerHelper = $customerHelper;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validate($customer)
    {
        if ($this->otpHelper->isModuleEnable() &&
            CustomerHelper::USERNAME_EMAIL !== $this->customerHelper->getCurrentUsernameType()
        ) {
            $phoneNumber = $customer->loadByEmail($customer->getEmail())
                ->getData($this->getAttribute()->getAttributeCode());
            $result = $this->customerHelper->validatePhonenumber($phoneNumber, $customer->getId());
            if ($result['errors']) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    $result['messages'][CustomerHelper::PHONENUMBER_INVALID_FORMAT]
                        ?? $result['messages'][CustomerHelper::PHONENUMBER_ALREADY_EXISTS]
                            ?? __('Invalid phone number.')
                );
            }
        }
        return true;
    }
}
