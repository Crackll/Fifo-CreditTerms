<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Price
 */
class Name extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;
    
    /**
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    protected $helper;
    
    /**
     * Constructor
     *
     * @param ContextInterface                   $context
     * @param UiComponentFactory                 $uiComponentFactory
     * @param PriceCurrencyInterface             $priceFormatter
     * @param \Webkul\MpWalletSystem\Helper\Data $helper,
     * @param \Magento\Customer\Model\Customer   $customerModel,
     * @param array                              $components
     * @param array                              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        \Webkul\MpWalletSystem\Helper\Data $helper,
        \Magento\Customer\Model\Customer $customerModel,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->customerModel = $customerModel;
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customerId = $item['customer_id'];
                $customer = $this->loadCustomer($customerId);
                $item['customerName'] = $customer->getFirstname().' '.$customer->getLastname();
                $item['email'] = $customer->getEmail();
            }
        }
        return $dataSource;
    }

    /**
     * Load Customer By customer Id
     *
     * @param int $customerId
     * @return object \Magento\Customer\Model\Customer
     */
    public function loadCustomer($customerId)
    {
        return $this->customerModel->load($customerId);
    }
}
