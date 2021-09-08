<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Enable extends Column
{

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['enable'] == 0) {
                    $item['enable'] = '<div style="background:#f9d4d4;border:1px solid;
                    border-color:#e22626;padding: 0 7px;text-align:center;text-transform: uppercase;
                    color:#e22626;font-weight:bold;" title="Ad is disable by admin">Disable</div>';
                } else {
                    $item['enable'] = '<div style="background:#d0e5a9;border:1px solid;border-color:#5b8116;
                    padding: 0 7px;text-align:center;text-transform: uppercase;color:#185b00;font-weight:bold;
                    " title="Ad is enable by admin">Enable</div>';
                }
            }
        }
        return $dataSource;
    }
}
