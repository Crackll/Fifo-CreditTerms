<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Ui\Component\Listing\Column\Rewardcart;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Customer\Model\CustomerFactory;

class Sellername extends Column
{
    /**
     * Constructor
     *
     * @param ContextInterface    $context
     * @param CustomerFactory     $customerFactory
     * @param UiComponentFactory  $uiComponentFactory
     * @param array               $components
     * @param array               $data
     */
    public function __construct(
        ContextInterface $context,
        CustomerFactory $customerFactory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_customerModel = $customerFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item['seller_id']) {
                    $customer = $this->_customerModel->create()
                                      ->load($item['seller_id']);
                    $item[$this->getData('name')] = $customer->getFirstname()." ".$customer->getLastname();
                } else {
                    $item[$this->getData('name')] = "Admin";
                }
            }
        }
        return $dataSource;
    }
}
