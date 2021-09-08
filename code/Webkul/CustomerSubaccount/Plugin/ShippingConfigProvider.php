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

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter;

/**
 * Plugin class.
 */
class ShippingConfigProvider
{
    /**
     * Customer Repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * Customer Address Data Formatter
     *
     * @var \Magento\Customer\Model\Address\CustomerAddressDataFormatter
     */
    public $customerAddressDataFormatter;
    
    /**
     * Helper
     *
     * @var \Webkul\CustomerSubaccount\Helper\Data
     */
    public $helper;

    /**
     * Constructor
     *
     * @param CustomerRepository $customerRepository
     * @param CustomerAddressDataFormatter $customerAddressDataFormatter
     * @param \Webkul\CustomerSubaccount\Helper\Data $helper
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerAddressDataFormatter $customerAddressDataFormatter,
        \Webkul\CustomerSubaccount\Helper\Data $helper
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerAddressDataFormatter = $customerAddressDataFormatter;
        $this->helper = $helper;
    }

    /**
     * After Get Config Plugin
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, $result)
    {
        $customerId = $this->helper->getCustomerId();
        if ($this->helper->isForcedMainAddress($customerId)) {
            $mainAccId = $this->helper->getMainAccountId($customerId);
            $customer = $this->customerRepository->getById($mainAccId);
            $customerData = $customer->__toArray();
            $customerOriginAddresses = $customer->getAddresses();
            $customerAddresses = [];
            if ($customerOriginAddresses) {
                foreach ($customerOriginAddresses as $address) {
                    $customerAddresses[$address->getId()] = $this->customerAddressDataFormatter->prepareAddress(
                        $address
                    );
                }
            }
            $customerData['addresses'] = $customerAddresses;
            $result['customerData'] = $customerData;
            $result['wkForcedMainAddress'] = 1;
        } else {
            $result['wkForcedMainAddress'] = 0;
        }
        return $result;
    }
}
