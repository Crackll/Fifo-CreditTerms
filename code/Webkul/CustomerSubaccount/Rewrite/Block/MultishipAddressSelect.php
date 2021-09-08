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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Helper\Address as CustomerAddressHelper;
use Magento\Customer\Api\AddressRepositoryInterface;

class MultishipAddressSelect extends \Magento\Multishipping\Block\Checkout\Address\Select
{
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param CustomerAddressHelper $customerAddressHelper
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        CustomerAddressHelper $customerAddressHelper,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Webkul\CustomerSubaccount\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customerAddressHelper = $customerAddressHelper;
        $this->addressMapper = $addressMapper;
        $this->addressRepository = $addressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $multishipping,
            $customerAddressHelper,
            $addressMapper,
            $addressRepository,
            $searchCriteriaBuilder,
            $filterBuilder,
            $data
        );
    }

    /**
     * Get a list of current customer addresses.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getAddress()
    {
        $addresses = $this->getData('address_collection');
        if ($addresses === null) {
            try {
                $customerId = $this->_multishipping->getCustomer()->getId();
                if ($this->helper->isForcedMainAddress()) {
                    $customerId = $this->helper->getMainAccountId();
                }
                $filter =  $this->filterBuilder->setField('parent_id')
                    ->setValue($customerId)
                    ->setConditionType('eq')
                    ->create();
                $addresses = (array)($this->addressRepository->getList(
                    $this->searchCriteriaBuilder->addFilters([$filter])->create()
                )->getItems());
            } catch (NoSuchEntityException $e) {
                return [];
            }
            $this->setData('address_collection', $addresses);
        }
        return $addresses;
    }
}
