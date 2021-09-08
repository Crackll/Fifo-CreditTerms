<?php
namespace Webkul\CustomerApproval\Ui\Component\Listing\Column;
  
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
  
class ShowIsApproved extends Column
{
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
                if ($item['wk_customer_approval'] === null) {
                    $item['wk_customer_approval'] = __('Pending');
                } else {
                    if ($item['wk_customer_approval'][0] == '1') {
                        $item['wk_customer_approval'] = __('Approved');
                    } elseif ($item['wk_customer_approval'][0] == '0') {
                        $item['wk_customer_approval'] = __('Pending');
                    } else {
                        $item['wk_customer_approval'] = __('Rejected');
                    }
                }
            }
        }
        return $dataSource;
    }
}
