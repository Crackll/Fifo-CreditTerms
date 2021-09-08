<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_CustomerSubaccount
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Plugin;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\AddressFactory;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;

class AddressValidator
{
    public $helper;

    public function __construct(
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        AddressFactory $addressFactory
    ) {
        $this->helper = $helper;
        $this->addressFactory = $addressFactory;
    }

    public function afterValidate(
        \Magento\Customer\Model\Address\Validator\Customer $subject,
        $result,
        AbstractAddress $address
    ) {
        $addressId = $address instanceof QuoteAddressInterface ? $address->getCustomerAddressId() : $address->getId();
        if ($addressId !== null) {
            $addressCustomerId = (int) $address->getCustomerId();
            $originalAddressCustomerId = (int) $this->addressFactory->create()
                ->load($addressId)
                ->getCustomerId();
            $mainAccountId = $this->helper->getMainAccountId();

            if ($originalAddressCustomerId !== 0
                && ($originalAddressCustomerId !== $addressCustomerId)
            ) {
                if (!empty($result) && $originalAddressCustomerId == $mainAccountId) {
                    array_pop($result);
                }
            }
        }

        return $result;
    }
}
