<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Block\Adminhtml;

use Webkul\MpWholesale\Api\QuoteRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

class SellerDetails extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param AddressRepositoryInterface $addressRepository
     * @param QuoteRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Block\Address\Book $addressBook
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Customer $customerModel,
        AddressRepositoryInterface $addressRepository,
        QuoteRepositoryInterface $quoteRepository,
        \Magento\Customer\Block\Address\Book $addressBook,
        array $data = []
    ) {
        $this->customerModel = $customerModel;
        $this->addressRepository = $addressRepository;
        $this->quoteRepository = $quoteRepository;
        $this->addressBook = $addressBook;
        parent::__construct($context, $data);
    }
    /**
     * customer data by customer id.
     *
     * @return object
     */
    public function getCustomerData($customerId)
    {
        return $this->customerModel->load($customerId);
    }
    /**
     * get quote load by id
     */
    public function getQuoteData()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $quoteModel = $this->quoteRepository->getById($quoteId);
        return $quoteModel;
    }

    /**
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getAddressById($addressId)
    {
        try {
            return $this->addressRepository->getById($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressHtml(\Magento\Customer\Api\Data\AddressInterface $address = null)
    {
        return $this->addressBook->getAddressHtml($address);
    }
}
