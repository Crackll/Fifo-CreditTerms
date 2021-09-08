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
namespace Webkul\CustomerSubaccount\Rewrite\Block;

use Magento\Customer\Model\Address\Config as AddressConfig;

class MultishipAddress extends \Magento\Multishipping\Block\Checkout\Addresses
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param AddressConfig $addressConfig
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AddressConfig $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        array $data = []
    ) {
        $this->_filterGridFactory = $filterGridFactory;
        $this->_multishipping = $multishipping;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->coreSession = $coreSession;
        $this->_isScopePrivate = true;
        parent::__construct(
            $context,
            $filterGridFactory,
            $multishipping,
            $customerRepository,
            $addressConfig,
            $addressMapper,
            $data
        );
    }

    /**
     * Retrieve options for addresses dropdown
     *
     * @return array
     */
    public function getAddressOptions()
    {
        $multishipForced = false;
        if ($this->helper->isForcedMainAddress()) {
            $multishipForced = true;
        }
        $options = $this->getData('address_options');
        if ($options === null) {
            $options = [];
            $addresses = [];

            try {
                $customerId = $this->getCustomerId();
                if ($this->helper->isForcedMainAddress()) {
                    $customerId = $this->helper->getMainAccountId();
                }
                $addresses = $this->customerRepository->getById($customerId)->getAddresses();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                /** Customer does not exist */
            }
            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            foreach ($addresses as $address) {
                $label = $this->_addressConfig
                    ->getFormatByCode(AddressConfig::DEFAULT_ADDRESS_FORMAT)
                    ->getRenderer()
                    ->renderArray($this->addressMapper->toFlatArray($address));

                $options[] = [
                    'value' => $address->getId(),
                    'label' => $label,
                ];
            }
            $this->setData('address_options', $options);
        }
        $this->coreSession->setMultishipForced($multishipForced);
        return $options;
    }
}
